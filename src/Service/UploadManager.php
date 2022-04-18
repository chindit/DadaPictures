<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Pack;
use App\Entity\Picture;
use App\Model\Status;
use Chindit\Archive\Archive;
use Chindit\Archive\Exception\UnsupportedArchiveType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Uuid;

class UploadManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private FileManager $fileManager,
        private PackManager $packManager,
        private Path $path,
        private PictureConverter $pictureConverter,
        private Filesystem $filesystem,
    ) {
    }

	/**
	 * @param array|UploadedFile[] $files
	 */
    public function moveUploadFilesToTempStorage(array $files): string
    {
        if (empty($files)) {
            return '';
        }

        $temporaryDirectory = Uuid::v4();

        // Exception is thrown if directory is not created
        $this->filesystem->mkdir($this->path->getTempDirectory() . $temporaryDirectory);

        $concurrentDirectory = $this->path->getTempDirectory() . $temporaryDirectory . '/';

        foreach ($files as $file) {
            if (!$file->isReadable() || $file->getRealPath() === false) {
                throw new IOException(sprintf('Unable to read %s file', $file->getRealPath()));
            }

            $newFileName = $this->fileManager->getUniqueFileName($file);
            $this->filesystem->rename($file->getRealPath(), $concurrentDirectory . $newFileName);
        }

        return (string)$temporaryDirectory;
    }

    /**
     * Upload a pack and extract it
     */
    public function upload(Pack $pack): Pack
    {
	    $storageDirectory = $this->path->getTempDirectory() . $pack->getStoragePath();

		$fileList = (new Finder())
		    ->ignoreDotFiles(true)
		    ->ignoreUnreadableDirs()
		    ->followLinks()
		    ->depth('< 30')
		    ->ignoreVCS(true)
		    ->in($storageDirectory)
		    ->files();

		// Unpack archive(s) if present
		foreach ($fileList as $file) {
		    $file = new File($file->getPathname());

            if (!exif_imagetype($file->getPathname())) {
                if (Archive::isSupportedArchive($file->getPathname())) {
                    Archive::extract($file->getPathname(), $storageDirectory);
                } else {
                    throw new UnsupportedArchiveType(sprintf("File %s of type %s is not supported", $file->getPathname(), $file->getMimeType()));
                }
            }
	    }

	    $pack->setStatus(Status::TEMPORARY);
	    $this->entityManager->persist($pack);

        $this->uploadFiles($pack);

        $pack = $this->packManager->checkPackStatus($pack);
        $this->entityManager->flush();

        return $pack;
    }

    /**
     * Upload a directory of files
     * @deprecated
     */
    public function uploadFileDir(string $fileDir, Pack $pack): Pack
    {
        if (!is_dir($fileDir)) {
            throw new FileNotFoundException($fileDir);
        }
        $pack->setStoragePath($fileDir);
        $pack->setStatus(Status::TEMPORARY);
        $this->uploadFiles($pack);
        $pack = $this->packManager->checkPackStatus($pack);
        $pack->setCreator($this->security->getUser());
        $this->entityManager->persist($pack);
        $this->entityManager->flush();

        return $pack;
    }

    /**
     * Upload files contained in pack
     */
    public function uploadFiles(Pack $pack): void
    {
        $fileList = (new Finder())
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->followLinks()
            ->depth('< 30')
            ->ignoreVCS(true)
            ->in($this->path->getTempDirectory() . $pack->getStoragePath())
            ->files()
        ;

        foreach ($fileList as $file) {
            $picture = $this->fileManager->hydrateFileFromPath($file, $pack->getCreator());
            if (in_array($picture->getStatus(), [Status::WARNING, Status::ERROR])) {
                continue;
            }
            $pack->addPicture($picture);
            $this->entityManager->persist($picture);
        }
    }

    /**
     * Validate upload and transfer files
     */
    public function validateUpload(Pack $pack): bool
    {
        if (!$this->checkFiles($pack)) {
            $pack->setStatus(Status::ERROR);
            $this->entityManager->flush();
            return false;
        }

        $newStoragePath = $this->fileManager->prepareDestinationDir($pack);

        foreach ($pack->getPictures() as $picture) {
            /** @var $picture Picture */
            if ($picture->getStatus() !== Status::TEMPORARY) {
                continue;
            }

            $picture = $this->fileManager->getPictureHashes($picture);

            $duplicate = $this->fileManager->findDuplicates($picture);
            if ($duplicate !== null) {
                $picture->setStatus(Status::DUPLICATE);
                $pack->addPicture($duplicate);
                continue;
            }

            $imagesize = getimagesize($this->path->getTempDirectory() . $picture->getFilename());
            if ($imagesize === false) {
                return false;
            }

            [$width, $height] = $imagesize;
            if ($width === null || $height === null) {
                $picture->setStatus(Status::ERROR);
                continue;
            }
            $picture->setWidth($width);
            $picture->setHeight($height);
            $this->pictureConverter->createThumbnail($picture);
            $picture->setWeight(filesize($this->path->getTempDirectory() . $picture->getFilename()) ?: 0);

            $picture->setStatus(Status::OK);
            $picture->setStatusInfo('OK');

            if (in_array($picture->getMime(), ['image/tiff', 'image/jpeg'])) {
                $exif = exif_read_data($this->path->getTempDirectory() . $picture->getFilename(), 'COMPUTED,IFD0,COMMENT,EXIF');
                if ($exif !== false) {
                    $picture->setProperties($exif);
                }
            }

            $picture = $this->fileManager->moveFileToPack($picture, $pack, $newStoragePath);

            $picture->setFilename(
                substr($newStoragePath, strrpos($newStoragePath, '/') + 1) . '/' . basename($picture->getFilename())
            );

            $this->entityManager->persist($picture);
        }

        $this->fileManager->cleanStorage($pack->getStoragePath());
        $this->packManager->removeObsoletePictures($pack);

		$oldStoragePack = $pack->getStoragePath();

        $pack->setStatus(Status::OK);
        $pack->setStoragePath($newStoragePath);
        $this->entityManager->flush();

		// Remove temporary files
	    $this->fileManager->cleanStorage($oldStoragePack);

        return true;
    }

    private function checkFiles(Pack $pack): bool
    {
        /** @var Picture $picture */
        foreach ($pack->getPictures() as $picture) {
            if (!is_file($this->path->getTempDirectory() . $picture->getFilename())) {
                $picture->setStatusInfo(sprintf('File not found.  Looked in %s', $this->path->getTempDirectory() . $picture->getFilename()));
                return false;
            }
        }

        return true;
    }
}

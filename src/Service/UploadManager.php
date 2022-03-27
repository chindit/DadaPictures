<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Pack;
use App\Entity\Picture;
use App\Factory\ArchiveFactory;
use App\Model\Status;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnreadableFileEncountered;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
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
    ) {
    }

    public function moveUploadFilesToTempStorage(array $files): string
    {
        if (empty($files)) {
            return '';
        }

        $temporaryDirectory = Uuid::v4();
        if (!mkdir($concurrentDirectory = $this->path->getTempDirectory() . $temporaryDirectory) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $concurrentDirectory .= '/';

        foreach ($files as $file) {
            if (!$file->isReadable() || $file->getRealPath() === false) {
                throw new UnreadableFileEncountered(sprintf('Unable to read %s file', $file->getRealPath()));
            }

            $stream = fopen($file->getRealPath(), 'rb+');

            if ($stream === false) {
                throw new UnreadableFileEncountered(sprintf('Unable to read %s file', $file->getRealPath()));
            }

            $newFileName = uniqid('temp_upload_', true) . '.' . $file->guessExtension();

            if (file_put_contents($concurrentDirectory . $newFileName, $stream) === false) {
                throw new UnableToWriteFile(sprintf('File %s couln\'t be written to temp storage', $concurrentDirectory . $newFileName));
            }

            fclose($stream);
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
		    $handler = ArchiveFactory::getHandler($file);
			if ($handler) {
				$handler->extractArchive($file, $storageDirectory);
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
     * @deprecated
     * Remove uploaded pack once it is extracted
     */
    public function deleteFTPFile(File $file): bool
    {
        return unlink($file->getFilename());
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
        // Temporary avoid to remove "obsolete" pictures
        // $this->packManager->removeObsoletePictures($pack);

        $pack->setStatus(Status::OK);
        $pack->setStoragePath($newStoragePath);
        $this->entityManager->flush();

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

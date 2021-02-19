<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Pack;
use App\Entity\Picture;
use App\Factory\ArchiveFactory;
use App\Model\Status;
use App\Repository\BannedPictureRepository;
use App\Service\ArchiveHandler\ArchiveHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnreadableFileEncountered;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Security;

class UploadManager
{
    private ArchiveHandlerInterface $handler;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private FileManager $fileManager,
        private PackManager $packManager,
        private Path $path,
        private BannedPictureRepository $bannedPictureRepository,
        private PictureConverter $pictureConverter
    ) {
    }

    public function moveUploadFileToTempStorage(?File $file): string
    {
        if ($file === null) {
            return '';
        }

        if (!$file->isReadable() || $file->getRealPath() === false) {
            throw new UnreadableFileEncountered(sprintf('Unable to read %s file', $file->getRealPath()));
        }

        $stream = fopen($file->getRealPath(), 'rb+');
        if ($stream === false) {
            throw new UnreadableFileEncountered(sprintf('Unable to read %s file', $file->getRealPath()));
        }
        $newFileName = uniqid('temp_upload_', true) . '.' . $file->guessExtension();
        file_put_contents($this->path->getTempUploadDirectory() . $newFileName, $stream);
        fclose($stream);

        return $newFileName;
    }

    /**
     * Upload a pack and extract it
     */
    public function upload(Pack $pack): Pack
    {
    	$file = new File($this->path->getTempUploadDirectory() . $pack->getStoragePath());
        $this->handler = ArchiveFactory::getHandler($file);
        $pack->setStoragePath($this->fileManager->createTempUploadDirectory());
        $this->handler->extractArchive($file, $this->path->getTempDirectory() . $pack->getStoragePath());
        $pack->setStatus(Status::TEMPORARY);
        $this->entityManager->persist($pack);

        $this->uploadFiles($pack);

        $pack = $this->packManager->checkPackStatus($pack);
        $this->entityManager->flush();

        return $pack;
    }

    /**
     * Upload a directory of files
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
            $picture = $this->fileManager->hydrateFileFromPath($file);
            if (in_array($picture->getStatus(), [Status::WARNING, Status::ERROR])) {
                continue;
            }
            $pack->addPicture($picture);
            $this->entityManager->persist($picture);
        }
    }

    /**
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
        $newStoragePath = $this->fileManager->prepareDestinationDir($pack);

        foreach ($pack->getPictures() as $picture) {
            /** @var $picture Picture */
            if ($picture->getStatus() !== Status::TEMPORARY) {
                continue;
            }

            $picture = $this->fileManager->getPictureHashes($picture);

            if ($this->fileManager->findDuplicates($picture)) {
                $picture->setStatus(Status::DUPLICATE);
                continue;
            }

            [$width, $height] = getimagesize($this->path->getTempDirectory() . $picture->getFilename());
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

        $pack->setStatus(Status::OK);
        $pack->setStoragePath($newStoragePath);
        $this->entityManager->flush();

        return true;
    }
}

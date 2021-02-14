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
        private FilesystemOperator $temporaryStorage,
        private Path $path,
        private BannedPictureRepository $bannedPictureRepository
    ) {
    }

    public function moveUploadFileToTempStorage(File $file): string
    {
        if (!$file->isReadable() || $file->getRealPath() === false) {
            throw new UnreadableFileEncountered(sprintf('Unable to read %s file', $file->getRealPath()));
        }

        $stream = fopen($file->getRealPath(), 'rb+');
        if ($stream === false) {
            throw new UnreadableFileEncountered(sprintf('Unable to read %s file', $file->getRealPath()));
        }
        $newFileName = uniqid('temp_upload_', true);
        $this->temporaryStorage->writeStream($newFileName, $stream);
        fclose($stream);

        return $newFileName;
    }

    /**
     * Upload a pack and extract it
     */
    public function upload(Pack $pack): Pack
    {
        if ($pack->getFile() === null) {
            throw new FileNotFoundException(sprintf('Pack %s does not contain a file for upload', $pack->getName()));
        }
        $this->handler = ArchiveFactory::getHandler($pack->getFile());
        $pack->setStoragePath($this->fileManager->createTempUploadDirectory());
        $this->handler->extractArchive($pack->getFile(), $this->path->getTempDirectory() . $pack->getStoragePath());
        $pack->setStatus(Status::TEMPORARY);
        $this->entityManager->persist($pack);

        $this->uploadFiles($pack);

        $pack = $this->packManager->checkPackStatus($pack);
        $pack->setCreator($this->security->getUser());
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
            if ($picture->getStatus() !== Status::OK && $picture->getStatus() !== Status::TEMPORARY) {
                return false;
            }

            $picture = $this->fileManager->getPictureHashes($picture);

            if ($this->fileManager->findDuplicates($picture)) {
                return false;
            }

            [$width, $height] = getimagesize($this->path->getTempDirectory() . $picture->getFilename());
            $picture->setWidth($width);
            $picture->setHeight($height);
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

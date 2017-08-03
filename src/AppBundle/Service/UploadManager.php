<?php
declare(strict_type=1);

namespace AppBundle\Service;

use AppBundle\Entity\Pack;
use AppBundle\Entity\Picture;
use AppBundle\Factory\ArchiveFactory;
use AppBundle\Model\Status;
use AppBundle\Service\ArchiveHandler\ArchiveHandlerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UploadManager
 * @package AppBundle\Service
 */
class UploadManager
{
    /** @var ArchiveHandlerInterface */
    private $handler;

    /** @var EntityManager */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var FileManager */
    private $fileManager;

    /** @var PackManager */
    private $packManager;

    /**
     * UploadManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param array $allowedPictureType
     * @param string $kernelRootDir
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        FileManager $fileManager,
        PackManager $packManager)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->fileManager = $fileManager;
        $this->packManager = $packManager;
    }

    /**
     * Upload a pack and extract it
     * @param Pack $pack
     * @return Pack
     */
    public function upload(Pack $pack): Pack
    {
        $this->handler = ArchiveFactory::getHandler($pack->getFile());
        $pack->setStoragePath($this->fileManager->createTempUploadDirectory());
        $this->handler->extractArchive($pack->getFile(), $pack->getStoragePath());
        $pack->setStatus(Status::TEMPORARY);
        $this->entityManager->persist($pack);
        $this->uploadFiles($pack);
        $pack = $this->packManager->checkPackStatus($pack);
        $pack->setCreator($this->tokenStorage->getToken()->getUser());
        $this->entityManager->flush();

        return $pack;
    }

    /**
     * Upload files contained in pack
     * @param Pack $pack
     */
    public function uploadFiles(Pack $pack): void
    {
        $fileList = $this->fileManager->getFilesFromDir($pack->getStoragePath());
        foreach ($fileList as $file) {
            $picture = $this->fileManager->hydrateFileFromPath($file);
            $picture->setPack($pack);
            $this->entityManager->persist($picture);
        }
    }

    /**
     * Validate upload and transfer files
     * @param Pack $pack
     * @param ArrayCollection $pictures
     * @return bool
     */
    public function validateUpload(Pack $pack, ArrayCollection $pictures) : bool
    {
        $newStoragePath = $this->fileManager->prepareDestinationDir();

        foreach ($pictures as $picture) {
            /** @var $picture Picture */
            if ($picture->getStatus() !== Status::OK && $picture->getStatus() !== Status::TEMPORARY) {
                return false;
            }

            $picture = $this->fileManager->getPictureHashes($picture);

            if ($this->fileManager->findDuplicates($picture)) {
                return false;
            }

            list($width, $height) = getimagesize($picture->getFilename());
            $picture->setWidth($width);
            $picture->setHeight($height);
            $picture->setWeight(filesize($picture->getFilename()));

            $picture->setStatus(Status::OK);
            $picture->setStatusInfo('OK');

            if (in_array($picture->getMime(), ['image/png', 'image/jpeg'])) {
                $exif = exif_read_data($picture->getFilename(), 'COMPUTED,IFD0,COMMENT,EXIF');
                if ($exif !== false) {
                    $picture->setProperties($exif);
                }
            }

            $picture = $this->fileManager->moveFileToPack($picture, $newStoragePath);

            $picture->setFilename(substr($newStoragePath, strrpos($newStoragePath, '/')+1) . '/' . basename($picture->getFilename()));

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

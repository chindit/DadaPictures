<?php
declare(strict_type=1);

namespace AppBundle\Service;

use AppBundle\Entity\Pack;
use AppBundle\Factory\ArchiveFactory;
use AppBundle\Model\Status;
use AppBundle\Service\ArchiveHandler\ArchiveHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class UploadManager
 * @package AppBundle\Service
 */
class UploadManager
{
    /** @var ArchiveHandler */
    private $handler;

    /** @var EntityManager */
    private $entityManager;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var FileManager */
    private $fileManager;

    /** @var string */
    private $kernelRootDir;

    /**
     * UploadManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param array $allowedPictureType
     * @param string $kernelRootDir
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorage $tokenStorage, FileManager $fileManager, string $kernelRootDir)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->fileManager = $fileManager;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Upload a pack and extract it
     * @param Pack $pack
     * @return Pack
     */
    public function upload(Pack $pack) : Pack
    {
        $this->handler = ArchiveFactory::getHandler($pack->getFile());
        $pack->setStoragePath($this->createTempUploadDirectory());
        $this->handler->extractArchive($pack->getFile(), $pack->getStoragePath());
        $pack->setStatus(Status::TEMPORARY);
        $this->entityManager->persist($pack);
        $this->uploadFiles($pack);
        $this->entityManager->flush();

        return $pack;
    }

    /**
     * Upload files contained in pack
     * @param Pack $pack
     */
    public function uploadFiles(Pack $pack) : void
    {
        $fileList = $this->getFilesFromDir($pack->getStoragePath());
        foreach ($fileList as $file) {
            $picture = $this->fileManager->hydrateFileFromPath($file);
            $picture->setPack($pack);
            $this->entityManager->persist($picture);
        }
    }

    /**
     * List uploaded files
     * @param string $dir
     * @return array
     */
    private function getFilesFromDir(string $dir) : array
    {
        $iterator = new \DirectoryIterator($dir);

        $files = [];

        foreach ($iterator as $file) {
            if (is_dir($file->getPathname())) {
                if(strpos($file, '.') !== 0) {
                    $files = array_merge($this->getFilesFromDir($file->getPathname()), $files);
                }
            } else {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Create a temporary upload directory
     * @return string
     */
    private function createTempUploadDirectory() : string
    {
        $path = $this->kernelRootDir . '/../web/pictures/temp/';
        $dirName = uniqid('temp_');
        mkdir($path . $dirName);

        return $path . $dirName;
    }
}
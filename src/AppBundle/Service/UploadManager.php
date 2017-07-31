<?php
declare(strict_type=1);

namespace AppBundle\Service;

use AppBundle\Entity\Picture;
use AppBundle\Factory\ArchiveFactory;
use AppBundle\Interfaces\ArchiveHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;

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

    /** @var string */
    private $path;

    /** @var array */
    private $allowedPictureType;

    /** @var string */
    private $kernelRootDir;

    /**
     * UploadManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param array $allowedPictureType
     * @param string $kernelRootDir
     */
    public function __construct(EntityManagerInterface $entityManager, array $allowedPictureType, string $kernelRootDir)
    {
        $this->entityManager = $entityManager;
        $this->allowedPictureType = $allowedPictureType;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Handle upload, extract and first check for files
     * @param File $file
     * @return string
     */
    public function prepareUpload(File $file) : bool
    {
        $this->handler = ArchiveFactory::getHandler($file);
        if (!$this->handler) {
            return false;
        }
        $this->createTempUploadDirectory();
        $this->handler->extractArchive($file, $this->path);
        return true;
    }

    /**
     * Return pack extraction path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Fast check to see if files are valid or not
     * @param string $dir
     * @return array
     */
    public function checkFiles(string $dir) : array
    {
        $fileList = $this->getFilesFromDir($dir);
        $pictureRepository = $this->entityManager->getRepository(Picture::class);

        $response = [];
        foreach ($fileList as $file) {
            $sha1 = sha1_file($file);
            $md5 = md5_file($file);

            $currentFile = [];
            $currentFile['name'] = basename($file);

            $duplicate = $pictureRepository->findDuplicates($md5, $sha1);

            if ($duplicate) {
                $currentFile['status'] = 'danger';
                $currentFile['message'] = 'Duplicate of «' . $duplicate->getFilename() . '»';
                $response[] = $currentFile;
                continue;
            }

            $imageType = exif_imagetype($file);
            if ($imageType === false) {
                $currentFile['status'] = 'danger';
                $currentFile['message'] = 'Not a valid picture';
                $response[] = $currentFile;
                continue;
            }

            if (!in_array($imageType, $this->allowedPictureType)) {
                $currentFile['status'] = 'danger';
                $currentFile['message'] = 'Unsupported picture type';
                $response[] = $currentFile;
                continue;
            }

            $currentFile['message'] = 'OK';
            $currentFile['status'] = 'success';
            $response[] = $currentFile;
        }

        return $response;
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

        $this->path = $path . $dirName;

        return $this->path;
    }
}
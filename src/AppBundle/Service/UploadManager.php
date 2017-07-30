<?php
declare(strict_type=1);

namespace AppBundle\Service;

use AppBundle\Entity\Picture;
use AppBundle\Factory\ArchiveFactory;
use AppBundle\Interfaces\ArchiveHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;


class UploadManager
{
    /** @var ArchiveHandler */
    private $handler;

    /** @var EntityManager */
    private $entityManager;

    /** @var string */
    private $path;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function prepareUpload(File $file) : string
    {
        $this->handler = ArchiveFactory::getHandler($file);
        $this->path = $this->handler->extractArchive($file);
        return $this->path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function checkFiles(string $dir) : array
    {
        $fileList = $this->getFilesFromDir($dir);
        $pictureRepository = $this->entityManager->getRepository(Picture::class);

        $response = [];
        foreach ($fileList as $file) {
            $sha1 = sha1_file($file);
            $md5 = md5_file($file);

            $nbErrors = 0;
            $currentFile = [];
            $currentFile['name'] = basename($file);

            $duplicate = $pictureRepository->findDuplicates($md5, $sha1);

            if ($duplicate) {
                $nbErrors++;
                $currentFile['status'] = 'danger';
                $currentFile['message'] = 'Duplicate of «' . $duplicate->getFilename() . '»';
            } else {
                $currentFile['message'] = 'OK';
                $currentFile['status'] = 'success';
            }

            $response[] = $currentFile;
            /*switch (exif_imagetype($file)) {
                case IMAGETYPE_JPEG:
                    $exif = exif_read_data($file);
                    $size = 2;
                    break;
                default:
                    // Do nothing
            }*/
        }

        return $response;
    }

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
}
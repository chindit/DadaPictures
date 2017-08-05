<?php
declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Entity\Pack;
use AppBundle\Entity\Picture;
use AppBundle\Entity\User;
use AppBundle\Model\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FileManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var PictureManager */
    private $pictureManager;

    /** @var array */
    private $allowedPictureType;

    /** @var string */
    private $kernelRootDir;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, PictureManager $pictureManager, string $kernelRootDir)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->pictureManager = $pictureManager;
        $this->allowedPictureType = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG, IMAGETYPE_WEBP];
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Hydrate a picture from path
     * @param string $path
     * @return Picture
     */
    public function hydrateFileFromPath(string $path) : Picture
    {
        if (!is_file($path)) {
            throw new FileNotFoundException("File «" . $path . "» doesn't exist");
        }

        $picture = new Picture();

        if (!$this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->getUser() instanceof User) {
            throw new AccessDeniedException("User must be logged to access this resource");
        }

        $picture->setCreator($this->tokenStorage->getToken()->getUser());
        $picture->setName(basename($path));
        $picture->setFilename($path);

        if (!$this->isNameAllowed($path)) {
            $picture->setStatus(Status::WARNING);
            $picture->setStatusInfo("Filename is automatically skipped");

            return $picture;
        }

        $pictureType = exif_imagetype($path);

        if ($pictureType === false) {
            $picture->setStatus(Status::ERROR);
            $picture->setStatusInfo('Picture type is not recognized');

            return $picture;
        }

        if (!in_array($pictureType, $this->allowedPictureType, true)) {
            $picture->setStatus(Status::ERROR);
            $picture->setStatusInfo('Picture type is not allowed');

            return $picture;
        }

        $picture->setMime(mime_content_type($path));

        $picture = $this->getPictureHashes($picture);

        if ($duplicate = $this->findDuplicates($picture)) {
            $picture->setStatus(Status::ERROR);
            $picture->setStatusInfo('Picture is a duplicate of «' . $duplicate->getFilename() . '» from pack «'
            . $duplicate->getPack()->getName() . '»');

            return $picture;
        }

        $picture->setStatus(Status::TEMPORARY);
        $picture->setStatusInfo('OK');

        return $picture;
    }

    /**
     * List uploaded files
     * @param string $dir
     * @return array
     */
    public function getFilesFromDir(string $dir) : array
    {
        $iterator = new \DirectoryIterator($dir);

        $files = [];

        foreach ($iterator as $file) {
            if (is_dir($file->getPathname())) {
                if(strpos(basename($file->getPathname()), '.') !== 0) {
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
    public function createTempUploadDirectory() : string
    {
        $path = $this->kernelRootDir . '/../web/pictures/temp/';
        $dirName = uniqid('temp_');
        mkdir($path . $dirName);

        return $path . $dirName;
    }

    /**
     * Create destination directory
     * @param Pack $pack
     * @return string
     */
    public function prepareDestinationDir(Pack $pack) : string
    {
        $path = $this->kernelRootDir . '/../web/pictures/';
        $dirName = $this->cleanName($pack->getName());

        if (is_dir($path . $dirName)) {
            $dirName .= '_' . uniqid();
        }

        mkdir($path . $dirName);

        return $path . $dirName;
    }

    /**
     * Return duplicate of given picture if it exists
     * @param Picture $picture
     * @return Picture|null
     */
    public function findDuplicates(Picture $picture) : ?Picture
    {
        return $this->entityManager->getRepository(Picture::class)->findDuplicates($picture);
    }

    /**
     * Hydrate hashes for given picture
     * @param Picture $picture
     * @return Picture
     */
    public function getPictureHashes(Picture $picture) : Picture
    {
        list($md5, $sha1) = $this->pictureManager->getHashes($picture->getFilename());
        $picture->setMd5sum($md5);
        $picture->setSha1sum($sha1);

        return $picture;
    }

    /**
     * Move picture to a new destination
     * @param Picture $picture
     * @param string $destinationPath
     * @return Picture
     */
    public function moveFileToPack(Picture $picture, string $destinationPath) : Picture
    {
        if (!is_file($picture->getFilename())) {
            throw new FileNotFoundException($picture->getFilename());
        }

        if (!rename($picture->getFilename(), $destinationPath . '/' . basename($picture->getFilename()))) {
            throw new \RuntimeException("Unable to move file «" . $picture->getFilename() . '»');
        }

        $picture->setFilename($destinationPath . '/' . $this->cleanName(basename($picture->getFilename())));

        return $picture;
    }

    /**
     * Clean temporary storage
     * @param string $storagePath
     */
    public function cleanStorage(string $storagePath) : void
    {
        if (is_dir($storagePath)) {
            $files = scandir($storagePath);

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    if (is_dir($storagePath . '/' . $file)) {
                        $this->cleanStorage($storagePath . '/' . $file);
                    } else {
                        unlink($storagePath . '/' . $file);
                    }
                }
            }
            rmdir($storagePath);
        }
    }

    /**
     * Check if file can be indexed or not
     * @param string $path
     * @return bool
     */
    private function isNameAllowed(string $path) : bool
    {
        return !(strpos(basename($path), '.') === 0);
    }

    /**
     * Remove all non-alphanumeric characters from a string
     * @param string $filename
     * @return string
     */
    private function cleanName(string $filename) : string
    {
        return preg_replace("/[^.a-zA-Z0-9_-]+/", "",
            transliterator_transliterate('Any-Latin;Latin-ASCII;',
                str_replace(' ', '_', $filename)));
    }

}

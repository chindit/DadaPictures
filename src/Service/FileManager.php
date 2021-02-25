<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Pack;
use App\Entity\Picture;
use App\Entity\User;
use App\Model\Status;
use App\Repository\BannedPictureRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class FileManager
 * @package App\Service
 */
class FileManager
{
    /**
     * @var array|int[]
     */
    private array $allowedPictureType;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private PictureRepository $pictureRepository,
        private BannedPictureRepository $bannedPictureRepository,
        private Path $path
    ) {
        $this->allowedPictureType = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG, IMAGETYPE_WEBP];
    }

    /**
     * Hydrate a picture from path
     */
    public function hydrateFileFromPath(SplFileInfo $file, ?UserInterface $user = null): Picture
    {
        $picture = new Picture();

        if ($user === null && !$this->security->getUser() instanceof User) {
            throw new AccessDeniedException("User must be logged to access this resource");
        }

        //@phpstan-ignore-next-line
        $picture->setCreator($user ?? $this->security->getUser());
        $picture->setName($file->getBasename());
        $picture->setFilename(
            substr($file->getPathname(), strlen($this->path->getTempDirectory()), strlen($file->getPathname()))
        );

        $pictureType = exif_imagetype($file->getPathname());

        if ($pictureType === false) {
            $picture->setStatus(Status::ERROR);
            $picture->setStatusInfo('Picture type is not recognized');

            return $picture;
        }

        if (!in_array($pictureType, $this->allowedPictureType, true)) {
            // Try to convert picture
            $newPicturePath = PictureConverter::convertPicture($picture->getFilename());
            if (empty($newPicturePath)) {
                $picture->setStatus(Status::ERROR);
                $picture->setStatusInfo('Picture type is not allowed and can\'t be converted');

                return $picture;
            } else {
                $picture->setFilename($newPicturePath);
                $picture->setName(basename($newPicturePath)); // To get correct extension
            }
        }

        $picture->setMime(mime_content_type($file->getPathname()) ?: 'text/plain');

        $picture = $this->getPictureHashes($picture);

        if ($duplicate = $this->pictureRepository->findDuplicates($picture)) {
            $picture->setStatus(Status::DUPLICATE);
            $picture->setStatusInfo('Picture is a duplicate of «' . $duplicate->getFilename() . '»');

            return $picture;
        }

        if ($banned = $this->bannedPictureRepository->isBanned($picture->getSha1sum())) {
            $picture->setStatus(Status::ERROR);
            $picture->setStatusInfo('Picture is banned');

            return $picture;
        }

        $picture->setStatus(Status::TEMPORARY);
        $picture->setStatusInfo('OK');

        return $picture;
    }

    /**
     * Create a temporary upload directory
     */
    public function createTempUploadDirectory(): string
    {
        $dirName = uniqid('temp_', true);
        mkdir($this->path->getTempDirectory() . $dirName);

        return $dirName;
    }

    /**
     * Create destination directory
     * @param Pack $pack
     * @return string
     */
    public function prepareDestinationDir(Pack $pack): string
    {
        $dirName = $this->cleanName($pack->getName());

        $filesystem = new Filesystem();
        if ($filesystem->exists($this->path->getStorageDirectory() . $dirName)) {
            $dirName .= '_' . uniqid('', true);
        }

        $filesystem->mkdir($this->path->getStorageDirectory()  . $dirName);

        return $this->path->getStorageDirectory() . $dirName;
    }

    public function findDuplicates(Picture $picture): ?Picture
    {
        return $this->pictureRepository->findDuplicates($picture);
    }

    /**
     * Hydrate hashes for given picture
     * @param Picture $picture
     * @return Picture
     */
    public function getPictureHashes(Picture $picture): Picture
    {
        $picture->setSha1sum(sha1_file($this->path->getTempDirectory() . $picture->getFilename()) ?: 'error');

        return $picture;
    }

    /**
     * Move picture to a new destination
     * @param Picture $picture
     * @param string $destinationPath
     * @return Picture
     */
    public function moveFileToPack(Picture $picture, Pack $pack, string $destinationPath): Picture
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->path->getTempDirectory() . $picture->getFilename())) {
            throw new FileNotFoundException($picture->getFilename());
        }

        $newName = $this->cleanName($picture->getFilename());

        $filesystem->rename($this->path->getTempDirectory() . $picture->getFilename(), $destinationPath . '/' . $newName);

        $picture->setFilename($destinationPath . '/' . $newName);

        return $picture;
    }

    /**
     * Clean temporary storage
     * @param string $storagePath
     */
    public function cleanStorage(string $storagePath): void
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($storagePath)) {
            $files = array_diff(scandir($storagePath) ?: ['.', '..'], ['.', '..']);

            $filesystem->remove($files);

            $filesystem->remove($storagePath);
        }
    }

    /**
     * Delete physically a picture
     */
    public function deletePicture(Picture $picture): void
    {
        if (is_file($this->path->getTempDirectory() . $picture->getFilename())) {
            unlink($this->path->getTempDirectory() . $picture->getFilename());
        } elseif (is_file($this->path->getStorageDirectory() . $picture->getFilename())) {
            unlink($this->path->getStorageDirectory() . $picture->getFilename());
        }
    }

    /**
     * Remove all non-alphanumeric characters from a string
     */
    private function cleanName(string $filename): string
    {
        return preg_replace(
            "/[^.a-zA-Z0-9_-]+/",
            "",
            transliterator_transliterate(
                'Any-Latin;Latin-ASCII;',
                str_replace(' ', '_', basename($filename))
            )
            ?: uniqid('', true)
        )
            ?? uniqid('', true);
    }
}

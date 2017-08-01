<?php
declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Entity\Picture;
use AppBundle\Entity\User;
use AppBundle\Model\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FileManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var array */
    private $allowedPictureType;

    public function __construct(EntityManagerInterface $entityManager, TokenStorage $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->allowedPictureType = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG, IMAGETYPE_WEBP];
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

        $picture->setMd5sum(md5_file($path));
        $picture->setSha1sum(sha1_file($path));
        if ($duplicate = $this->entityManager->getRepository(Picture::class)->findDuplicates($picture)) {
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
     * Check if file can be indexed or not
     * @param string $path
     * @return bool
     */
    private function isNameAllowed(string $path) : bool
    {
        return !(strpos(basename($path), '.') === 0);
    }

}
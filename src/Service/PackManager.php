<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Pack;
use App\Model\Status;
use Doctrine\ORM\EntityManagerInterface;

class PackManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * PackManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Update pack status based on files it contains
     */
    public function checkPackStatus(Pack $pack): Pack
    {
        foreach ($pack->getPictures() as $picture) {
            if ($picture->getStatus() === Status::ERROR) {
                $pack->setStatus(Status::ERROR);

                return $pack;
            }
        }

        $pack->setStatus(Status::TEMPORARY);

        return $pack;
    }

    /**
     * Remove obsolete pictures from current pack
     * @param Pack $pack
     */
    public function removeObsoletePictures(Pack $pack): void
    {
        $pictures = $pack->getPictures();
        foreach ($pictures as $picture) {
            if ($picture->getStatus() !== Status::OK) {
                $this->entityManager->remove($picture);
            }
        }

        $this->entityManager->flush();

        return;
    }
}

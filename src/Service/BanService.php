<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\BannedPicture;
use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;

class BanService
{
	public function __construct(private EntityManagerInterface $entityManager)
	{

	}

	public function banPicture(Picture $picture): bool
	{
		$banPicture = new BannedPicture($picture->getSha1sum());
		$this->entityManager->persist($banPicture);
		$this->entityManager->remove($picture);

		$this->entityManager->flush();

		return true;
	}
}

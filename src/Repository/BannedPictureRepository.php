<?php

namespace App\Repository;

use App\Entity\BannedPicture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BannedPicture|null find($id, $lockMode = null, $lockVersion = null)
 * @method BannedPicture|null findOneBy(array $criteria, array $orderBy = null)
 * @method BannedPicture[]    findAll()
 * @method BannedPicture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<BannedPicture>
 */
class BannedPictureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BannedPicture::class);
    }

    public function isBanned(string $sha1): bool
    {
        return (int)$this->createQueryBuilder('bp')
            ->select('COUNT(bp.id)')
            ->where('bp.sha1 = :sha1')
            ->setParameter('sha1', $sha1)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}

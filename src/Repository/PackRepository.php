<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pack;
use App\Model\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pack>
 */
class PackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pack::class);
    }

    /**
     * @return array|Pack[]
     */
    public function getPacksInValidation(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Status::TEMPORARY)
            ->getQuery()
            ->getResult();
    }
}

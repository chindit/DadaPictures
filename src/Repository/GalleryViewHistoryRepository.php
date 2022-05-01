<?php

namespace App\Repository;

use App\Entity\GalleryViewHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GalleryViewHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method GalleryViewHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method GalleryViewHistory[]    findAll()
 * @method GalleryViewHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryViewHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GalleryViewHistory::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(GalleryViewHistory $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(GalleryViewHistory $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return GalleryViewHistory[] Returns an array of GalleryViewHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GalleryViewHistory
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Picture;
use App\Entity\Tag;
use App\Model\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PictureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Picture::class);
    }

    /**
     * Find duplicates for given picture
     */
    public function findDuplicates(Picture $picture): ?Picture
    {
        $sha1 = $picture->getSha1sum();

        return $this->createQueryBuilder('p')
            ->where('p.sha1sum = :sha1')
            ->andWhere('p.status = :status')
            ->setParameter('sha1', $sha1)
            ->setParameter('status', Status::OK)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return a picture without tag
     * @return Picture|null
     */
    public function getPictureWithoutTags(): ?Picture
    {
        return $this->createQueryBuilder('p')
            ->where('p.tags is empty')
            ->andWhere('p.status = :status')
            ->setParameter('status', Status::OK)
            ->orderBy('RAND()')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return all pictures belonging to this tag
     * @param Tag $tag
     * @return array|Picture[]
     */
    public function findRandomByTag(Tag $tag): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.tags', 't')
            ->where('t.id = :id')
            ->setParameter('id', $tag->getId())
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    /**
     * Return 50 random pictures
     * @return array|Picture[]
     */
    public function findRandom(): array
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', Picture::STATUS_OK)
            ->orderBy('RAND()')
            ->setMaxResults(50);

        return $query->getQuery()->getResult();
    }
}

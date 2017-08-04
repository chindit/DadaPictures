<?php
declare(strict_types=1);

namespace AppBundle\Repository;


use AppBundle\Entity\Picture;
use AppBundle\Entity\Tag;
use AppBundle\Model\Status;

/**
 * Class PictureRepository
 * @package AppBundle\Repository
 */
class PictureRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Find duplicates for given picture
     * @param Picture $picture
     * @return Picture|null
     */
    public function findDuplicates(Picture $picture) : ?Picture
    {
        $md5 = $picture->getMd5sum();
        $sha1 = $picture->getSha1sum();

        return $this->createQueryBuilder('p')
            ->where('(p.md5sum = :md5 OR p.sha1sum = :sha1)')
            ->andWhere('p.status = :status')
            ->setParameter('md5', $md5)
            ->setParameter('sha1', $sha1)
            ->setParameter('status', Status::OK)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return a picture without tag
     * @return Picture|null
     */
    public function getPictureWithoutTags() : ?Picture
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.tags', 't')
            ->where('t.id IS NULL')
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
     * @return array
     */
    public function findRandomByTag(Tag $tag) : array
    {
        $query = $this->createQueryBuilder('p');
        $query->join('p.tags', 't')
            ->where($query->expr()->eq('t.id', $tag->getId()))
            ->setMaxResults(50);
        return $query->getQuery()->getResult();
    }

    /**
     * Return 50 random pictures
     * @return array
     */
    public function findRandom() : array
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('RAND()')
            ->setMaxResults(50);

        return $query->getQuery()->getResult();
    }
}

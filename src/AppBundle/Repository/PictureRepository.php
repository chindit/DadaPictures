<?php
declare(strict_types=1);

namespace AppBundle\Repository;


use AppBundle\Entity\Picture;
use AppBundle\Entity\Tag;
use AppBundle\Model\Status;

class PictureRepository extends \Doctrine\ORM\EntityRepository
{
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

    public function getPictureWithoutTags()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.tags', 't')
            ->where('t.id IS NULL')
            ->andWhere('p.status = :status')
            ->setParameter('status', Status::OK)
            ->orderBy('p.id', 'rand')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    public function findRandomByTag(Tag $tag)
    {
        $query = $this->createQueryBuilder('p');
        $query->join('p.tags', 't')
            ->where($query->expr()->eq('t.id', $tag->getId()));
        return $query->getQuery()->getResult();
    }
}

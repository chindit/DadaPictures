<?php
declare(strict_types=1);

namespace AppBundle\Repository;


use AppBundle\Entity\Picture;

class PictureRepository extends \Doctrine\ORM\EntityRepository
{
    public function findDuplicates(Picture $picture) : ?Picture
    {
        $md5 = $picture->getMd5sum();
        $sha1 = $picture->getSha1sum();

        return $this->createQueryBuilder('p')
            ->where('p.md5sum = :md5')
            ->orWhere('p.sha1sum = :sha1')
            ->setParameter('md5', $md5)
            ->setParameter('sha1', $sha1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

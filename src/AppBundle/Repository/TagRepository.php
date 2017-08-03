<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Tag;

/**
 * TagRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TagRepository extends \Doctrine\ORM\EntityRepository
{
    public function getPictureCount(Tag $tag)
    {
        $query = $this->_em->createQuery('SELECT COUNT(p.id) FROM Picture p WHERE ?1 MEMBER OF p.tags');
        $query->setParameter(1, $tag->getId());
        return $query->getSingleScalarResult();
    }
}

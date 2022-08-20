<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pack;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /** @return array<int, Tag> */
	public function findDistinctForPack(Pack $pack): array
	{
        /** @var array<int, Tag> $results */
		$results = $this->createQueryBuilder('t')
			->join('t.pictures', 'p')
			->join('p.packs', 'g')
			->where('t.visible = true')
			->andWhere('g.id = :packId')
			->setParameter('packId', $pack->getId())
			->distinct()
			->getQuery()
			->getResult();

        return $results;
	}
}

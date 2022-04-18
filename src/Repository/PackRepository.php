<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pack;
use App\Entity\Tag;
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

	public function getPacksByTag(Tag $tag, int $page = 1): array
	{
		return $this->createQueryBuilder('g')
			->join('g.pictures', 'p')
			->join('p.tags', 't')
			->where('t.id = :id')
			->andWhere('g.status = :ok')
			->setParameter('id', $tag->getId())
			->setParameter('ok', Status::OK)
			->orderBy('g.created', 'DESC')
			->setFirstResult(($page-1)*25)
			->setMaxResults(25)
			->getQuery()
			->getResult();
	}
}

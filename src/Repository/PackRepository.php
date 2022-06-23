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
            ->where('c.status <> :status')
            ->andWhere('c.deletedAt is null')
            ->setParameter('status', Status::OK)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, Pack>
     */
    public function getPacksByTag(Tag $tag, int $page = 1): array
    {
        return $this->createQueryBuilder('g')
            ->join('g.pictures', 'p')
            ->join('p.tags', 't')
            ->where('t.id = :id')
            ->andWhere('g.deletedAt is null')
            ->andWhere('g.status = :ok')
            ->setParameter('id', $tag->getId())
            ->setParameter('ok', Status::OK)
            ->orderBy('g.created', 'DESC')
            ->setFirstResult(($page - 1) * 25)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

	/**
	 * @param int[] $includedTags
	 * @param int[] $excludedTags
	 * @param ?string $galleryName
	 *
	 * @return array<int, Pack>
	 */
	public function searchPacks(array $includedTags = [], array $excludedTags = [], ?string $galleryName = null, int $page = 1): array
	{
		$query = $this->createQueryBuilder('g')
			->join('g.pictures', 'p')
			->join('p.tags', 't')
			->where('g.deletedAt is null')
			->andWhere('p.deletedAt is null')
			->andWhere('g.status = :ok')
			->andWhere('p.status = :ok')
			->setParameter('ok', Status::OK);

		if (!empty($includedTags)) {
            $query->andWhere(':includedTags MEMBER OF p.tags')
                ->setParameter('includedTags', $includedTags);
		}

		if (!empty($excludedTags)) {
			$query->andWhere('t.id NOT IN (:excluded)')
				->setParameter('excluded', $excludedTags);
		}

		if ($galleryName) {
			$query->andWhere('g.name LIKE :name')
				->setParameter('name', '%' . $galleryName . '%');
		}
		$query
			->groupBy('g.id')
			->orderBy('g.id', 'DESC')
			->setFirstResult(($page - 1) * 25)
			->setMaxResults(25);

		return $query
			->getQuery()
			->getResult();
	}
}

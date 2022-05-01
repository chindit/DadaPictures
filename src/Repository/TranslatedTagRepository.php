<?php

namespace App\Repository;

use App\Entity\TranslatedTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TranslatedTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranslatedTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranslatedTag[]    findAll()
 * @method TranslatedTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<TranslatedTag>
 */
class TranslatedTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslatedTag::class);
    }

    /**
     * @param string $language
     *
     * @return array<int, mixed>
     */
    public function findAllWithTranslation(string $language): array
    {
        return $this->createQueryBuilder('tt')
            ->select(['tt.name', 't.id', 'SIZE(t.pictures) AS count'])
            ->join('tt.tag', 't')
            ->where('t.visible = true')
            ->andWhere('tt.language = :language')
            ->setParameter('language', $language)
            ->orderBy('SIZE(t.pictures)', 'DESC')
            ->getQuery()
            ->getScalarResult();
    }
}

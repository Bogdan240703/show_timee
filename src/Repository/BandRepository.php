<?php

namespace App\Repository;

use App\Entity\Band;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Band>
 */
class BandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Band::class);
    }

    //    /**
    //     * @return Band[] Returns an array of Band objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Band
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByFilters(?string $name, ?string $genre, ?string $sortField, string $sortDirection): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b');

        if ($name) {
            $qb->andWhere('LOWER(b.name) LIKE :name')
                ->setParameter('name', '%' . strtolower($name) . '%');
        }

        if ($genre) {
            $qb->andWhere('b.musicGenre = :genre')
                ->setParameter('genre', $genre);
        }
        $allowedFields = ['id', 'name'];
        if (in_array($sortField, $allowedFields)) {
            $qb->orderBy('b.' . $sortField, strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC');
        }
        return $qb;
    }

}

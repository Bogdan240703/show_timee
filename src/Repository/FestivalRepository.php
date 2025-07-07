<?php

namespace App\Repository;

use App\Entity\Festival;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Festival>
 */
class FestivalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Festival::class);
    }

    //    /**
    //     * @return Festival[] Returns an array of Festival objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Festival
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByFilters(?string $name, ?string $location, ?string $startDate, ?string $endDate, ?string $sortField, string $sortDirection): QueryBuilder
    {
        $qb = $this->createQueryBuilder('f');

        if ($name) {
            $qb->andWhere('f.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($location) {
            $qb->andWhere('f.location LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        if ($startDate) {
            try {
                $startDateObj = new \DateTime($startDate);
                $qb->andWhere('f.startDate >= :startDate')
                    ->setParameter('startDate', $startDateObj);
            } catch (\Exception $e) {
            }
        }

        if ($endDate) {
            try {
                $endDateObj = new \DateTime($endDate);
                $qb->andWhere('f.endDate <= :endDate')
                    ->setParameter('endDate', $endDateObj);
            } catch (\Exception $e) {
            }
        }
        $allowedFields = ['id', 'name', 'location', 'price', 'startDate', 'endDate'];
        if (in_array($sortField, $allowedFields)) {
            $qb->orderBy('f.' . $sortField, strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC');
        }

        return $qb;
    }
}

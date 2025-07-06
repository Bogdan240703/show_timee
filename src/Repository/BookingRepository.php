<?php

namespace App\Repository;

use App\Entity\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    //    /**
    //     * @return Booking[] Returns an array of Booking objects
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

    //    public function findOneBySomeField($value): ?Booking
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByFilters(?string $festivalName, ?string $email, ?string $fullName, string $sortField = 'id', string $sortDirection = 'asc'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.festival', 'f')
            ->addSelect('f');

        if ($festivalName) {
            $qb->andWhere('f.name LIKE :festivalName')
                ->setParameter('festivalName', '%' . $festivalName . '%');
        }

        if ($email) {
            $qb->andWhere('b.email LIKE :email')
                ->setParameter('email', '%' . $email . '%');
        }

        if ($fullName) {
            $qb->andWhere('b.fullname LIKE :fullName')
                ->setParameter('fullName', '%' . $fullName . '%');
        }
        $allowedFields = ['id', 'email', 'fullname'];
        if (in_array($sortField, $allowedFields)) {
            $qb->orderBy('f.' . $sortField, strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC');
        }
        return $qb;
//        return $qb->getQuery()->getResult();
    }

}

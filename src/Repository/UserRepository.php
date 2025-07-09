<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByFilters(?string $username, ?string $email, ?int $age, ?string $sortField, string $sortDirection)
    {
        $qb = $this->createQueryBuilder('u');

        if ($username) {
            $qb->andWhere('u.email LIKE :username')  // assuming 'username' is actually email here
            ->setParameter('username', '%' . $username . '%');
        }

        if ($email) {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%' . $email . '%');
        }

        if ($age !== null) {
            $qb->andWhere('u.age = :age')
                ->setParameter('age', $age);
        }

        $allowedSortFields = ['id', 'email', 'age']; // add 'username' if you have such a field

        if (in_array($sortField, $allowedSortFields, true)) {
            $qb->orderBy('u.' . $sortField, $sortDirection === 'desc' ? 'DESC' : 'ASC');
        } else {
            $qb->orderBy('u.id', 'ASC');
        }

        return $qb;
    }

}

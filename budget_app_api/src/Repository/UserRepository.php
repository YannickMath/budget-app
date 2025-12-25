<?php

namespace App\Repository;

use App\Entity\User;
use App\Trait\Repository\EntityPersistenceTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    use EntityPersistenceTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
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

    public function clearExpiredTokens(): int
    {
        $now = new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('u')
            ->update()
            ->set('u.password_reset_token', 'NULL')
            ->set('u.password_reset_token_expires_at', 'NULL')
            ->set('u.email_verification_token', 'NULL')
            ->set('u.email_verification_token_expires_at', 'NULL')
            ->where(
                '(u.password_reset_token_expires_at IS NOT NULL AND u.password_reset_token_expires_at < :now)'
                . ' OR (u.email_verification_token_expires_at IS NOT NULL AND u.email_verification_token_expires_at < :now)'
            )
            ->setParameter('now', $now);

        return $qb->getQuery()->execute();
    }

    public function countExpiredTokens(): int
    {
        $now = new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where(
                '(u.password_reset_token_expires_at IS NOT NULL AND u.password_reset_token_expires_at < :now)'
                . ' OR (u.email_verification_token_expires_at IS NOT NULL AND u.email_verification_token_expires_at < :now)'
            )
            ->setParameter('now', $now);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
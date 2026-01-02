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

    public function clearExpiredTokens(): int
    {
        $now = new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('u')
            ->update()
            ->set('u.passwordResetToken', 'NULL')
            ->set('u.passwordResetTokenExpiresAt', 'NULL')
            ->set('u.emailVerificationToken', 'NULL')
            ->set('u.emailVerificationTokenExpiresAt', 'NULL')
            ->where(
                '(u.passwordResetTokenExpiresAt IS NOT NULL AND u.passwordResetTokenExpiresAt < :now)'
                . ' OR (u.emailVerificationTokenExpiresAt IS NOT NULL AND u.emailVerificationTokenExpiresAt < :now)'
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
                '(u.passwordResetTokenExpiresAt IS NOT NULL AND u.passwordResetTokenExpiresAt < :now)'
                . ' OR (u.emailVerificationTokenExpiresAt IS NOT NULL AND u.emailVerificationTokenExpiresAt < :now)'
            )
            ->setParameter('now', $now);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\EmailChangeRequest;
use App\Trait\Repository\EntityPersistenceTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailChangeRequest>
 */
class EmailChangeRequestRepository extends ServiceEntityRepository
{
    use EntityPersistenceTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailChangeRequest::class);
    }

    /**
     * Count expired email change requests that are not confirmed
     */
    public function countExpiredRequests(): int
    {
        $now = new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.expiresAt < :now')
            ->andWhere('e.confirmedAt IS NULL')
            ->setParameter('now', $now);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Delete expired email change requests that are not confirmed
     */
    public function deleteExpiredRequests(): int
    {
        $now = new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('e')
            ->delete()
            ->where('e.expiresAt < :now')
            ->andWhere('e.confirmedAt IS NULL')
            ->setParameter('now', $now);

        return $qb->getQuery()->execute();
    }
}

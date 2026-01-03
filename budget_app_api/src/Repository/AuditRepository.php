<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Repository for managing audit entries cleanup
 */
final readonly class AuditRepository
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * Count audit entries older than specified days
     */
    public function countOldAudits(int $retentionDays): int
    {
        $cutoffDate = new \DateTimeImmutable("-{$retentionDays} days");

        $sql = 'SELECT COUNT(*) FROM users_audit WHERE created_at < :cutoff_date';

        return (int) $this->connection->fetchOne($sql, [
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Delete audit entries older than specified days
     */
    public function deleteOldAudits(int $retentionDays): int
    {
        $cutoffDate = new \DateTimeImmutable("-{$retentionDays} days");

        $sql = 'DELETE FROM users_audit WHERE created_at < :cutoff_date';

        return $this->connection->executeStatement($sql, [
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
        ]);
    }
}

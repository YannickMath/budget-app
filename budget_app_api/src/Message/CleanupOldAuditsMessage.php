<?php

namespace App\Message;

/**
 * Message to cleanup old audit entries from the database
 */
final readonly class CleanupOldAuditsMessage
{
    public function __construct(
        private int $retentionDays = 180  // 6 mois par défaut
    ) {}

    public function getRetentionDays(): int
    {
        return $this->retentionDays;
    }
}

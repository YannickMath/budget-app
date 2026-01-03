<?php

namespace App\MessageHandler;

use App\Message\CleanupOldAuditsMessage;
use App\Repository\AuditRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler for cleaning up old audit entries
 */
#[AsMessageHandler()]
final readonly class CleanupOldAuditsHandler
{
    public function __construct(
        private AuditRepository $auditRepository,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(CleanupOldAuditsMessage $message): void
    {
        $retentionDays = $message->getRetentionDays();

        $this->logger->info("Starting audit cleanup (retention: {$retentionDays} days)...");

        $count = $this->auditRepository->countOldAudits($retentionDays);
        $deleted = $this->auditRepository->deleteOldAudits($retentionDays);

        $this->logger->info("Audit cleanup completed. Found: {$count}, Deleted: {$deleted}");
    }
}

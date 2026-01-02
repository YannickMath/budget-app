<?php

namespace App\MessageHandler;

use App\Message\CleanupExpiredTokensMessage;
use App\Repository\EmailChangeRequestRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
final class CleanupExpiredTokensHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailChangeRequestRepository $emailChangeRequestRepository,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(CleanupExpiredTokensMessage $message): void
    {
        $this->logger->info('Starting expired tokens cleanup...');

        // Clean User tokens (email verification + password reset)
        $userTokensCount = $this->userRepository->countExpiredTokens();
        $userTokensCleared = $this->userRepository->clearExpiredTokens();
        $this->logger->info("User tokens cleanup: Found $userTokensCount, Cleared $userTokensCleared");

        // Clean EmailChangeRequest tokens
        $emailChangeCount = $this->emailChangeRequestRepository->countExpiredRequests();
        $emailChangeDeleted = $this->emailChangeRequestRepository->deleteExpiredRequests();
        $this->logger->info("Email change requests cleanup: Found $emailChangeCount, Deleted $emailChangeDeleted");

        $totalCleaned = $userTokensCleared + $emailChangeDeleted;
        $this->logger->info("Expired tokens cleanup completed. Total cleaned: $totalCleaned");
    }
}

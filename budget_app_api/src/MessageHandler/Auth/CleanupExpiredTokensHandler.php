<?php

namespace App\MessageHandler\Auth;

use App\Message\Auth\CleanupExpiredTokensMessage;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class CleanupExpiredTokensHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(CleanupExpiredTokensMessage $message): void
    {
        $this->logger->info('Starting expired tokens cleanup...');
        $count = $this->userRepository->countExpiredTokens();
        $cleared = $this->userRepository->clearExpiredTokens();
        $this->logger->info("Expired tokens cleanup completed. Found: $count, Cleared: $cleared");
    }
}
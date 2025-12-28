<?php

namespace App\Processor\User;

use App\Entity\User;
use App\Service\Admin\AdminUserService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Processor for admin deleting a user
 */
class UserDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly AdminUserService $adminUserService,
        private readonly Security $security,
    ) {}

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []): void
    {
        $userId = $uriVariables['id'] ?? null;

        if ($userId === null) {
            throw new \RuntimeException('User ID is required for deletion.');
        }

        $admin = $this->security->getUser();

        if (!$admin instanceof User) {
            throw new \RuntimeException('Admin user not found.');
        }

        // AdminUserService manages the soft delete
        $this->adminUserService->deleteUser($userId, $admin);
    }
}
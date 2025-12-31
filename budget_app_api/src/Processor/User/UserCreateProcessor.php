<?php

namespace App\Processor\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\Admin\Input\AdminCreateUserInputDTO;
use App\Entity\User;
use App\Service\Admin\AdminUserService;
use Psr\Log\LoggerInterface;

/**
 * Processor for admin creating new users
 * Different from public registration - allows setting roles, verified status, etc.
 */
class UserCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly AdminUserService $adminUserService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []): User
    {
        if (!$data instanceof AdminCreateUserInputDTO) {
            $this->logger->error('Invalid data type received in UserCreateProcessor', [
                'expected' => AdminCreateUserInputDTO::class,
                'received' => get_class($data),
            ]);
            throw new \InvalidArgumentException('Invalid input data: expected AdminCreateUserInputDTO');
        }

        $this->logger->info('Admin creating new user', [
            'email' => $data->email,
            'roles' => $data->roles,
        ]);

        try {
            $user = $this->adminUserService->createUser($data);

            $this->logger->info('User created successfully by admin', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
            ]);

            return $user;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create user', [
                'email' => $data->email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

}
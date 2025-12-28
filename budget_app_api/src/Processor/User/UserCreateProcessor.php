<?php

namespace App\Processor\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\Admin\Input\AdminCreateUserInputDTO;
use App\Entity\User;
use App\Service\Admin\AdminUserService;

/**
 * Processor for admin creating new users
 * Different from public registration - allows setting roles, verified status, etc.
 */
class UserCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly AdminUserService $adminUserService,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []): User
    {
        if (!$data instanceof AdminCreateUserInputDTO) {
            throw new \InvalidArgumentException('Invalid input data: expected AdminCreateUserInputDTO');
        }

        return $this->adminUserService->createUser($data);
    }

}
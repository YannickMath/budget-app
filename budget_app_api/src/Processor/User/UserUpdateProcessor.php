<?php

namespace App\Processor\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\Admin\Input\AdminUpdateUserInputDTO;
use App\Entity\User;
use App\Service\Admin\AdminUserService;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Processor for updating existing users (admin operation)
 * Uses AdminUserService for proper event handling and auditing
 */
class UserUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly AdminUserService $adminUserService,
        private readonly Security $security,
    ) {}

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): User {
        if (!$data instanceof AdminUpdateUserInputDTO) {
            throw new InvalidArgumentException('Invalid input data: expected AdminUpdateUserInputDTO');
        }

        $id = $uriVariables['id'] ?? null;
        if ($id === null) {
            throw new BadRequestHttpException('User ID is required for update');
        }

        // Get current admin user
        $admin = $this->security->getUser();
        if (!$admin instanceof User) {
            throw new AccessDeniedHttpException('Authentication required');
        }

        return $this->adminUserService->updateUser($id, $data, $admin);
    }
}

<?php

namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\User\Output\UserAttributesOutput;
use App\Service\UserService;

final class UserAttributesProvider implements ProviderInterface
{
    public function __construct(private UserService $userService) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * 
     * @return UserAttributesOutput|null
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): ?UserAttributesOutput {
        $id = $uriVariables['id'] ?? null;
        if (!$id) {
            return null;
        }
        $user = $this->userService->findOneById($id);
        if (!$user) {
            return null;
        }

        return $this->userService->toDetailsAttributesForUser($user);
    }
}
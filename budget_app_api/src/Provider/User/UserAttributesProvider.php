<?php

namespace App\Provider\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\User\Output\UserAttributesOutputDTO;
use App\Service\User\UserService;

final class UserAttributesProvider implements ProviderInterface
{
    public function __construct(private UserService $userService) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * 
     * @return UserAttributesOutputDTO|null
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): ?UserAttributesOutputDTO 
    {
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
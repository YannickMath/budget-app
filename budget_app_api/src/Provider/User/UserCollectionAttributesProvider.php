<?php

namespace App\Provider\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\User\Output\UserAttributesOutputDTO;
use App\Service\UserService;

final class UserCollectionAttributesProvider implements ProviderInterface
{
    public function __construct(private UserService $userService) {}
    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
    *
    * @return array<UserAttributesOutputDTO>|null
    */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
        ): ?array 
        {
        $users = $this->userService->findAll();
        if (!$users) {
            return null;
        }
        $userDTOs = [];
        foreach ($users as $user) {

            $userDTOs[] = $this->userService->toCollectionAttributesForUsers($users);

        }
        return $userDTOs;
    }
}
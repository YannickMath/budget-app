<?php

namespace App\Service;

use App\DTO\User\Output\UserAttributesOutput;
use App\Entity\User;
use App\Repository\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function findOneById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function toDetailsAttributesForUser(User $user): UserAttributesOutput
    {
        return new UserAttributesOutput(
            email: $user->getEmail(),
            username: $user->getUsername(),
            locale: $user->getLocale(),
            timezone: $user->getTimezone(),
            roles: $user->getRoles()
        );

    }
}
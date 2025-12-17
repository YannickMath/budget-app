<?php

namespace App\Service;

use App\DTO\User\Output\UserAttributesOutputDTO;
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

    public function toDetailsAttributesForUser(User $user): UserAttributesOutputDTO
    {
        return new UserAttributesOutputDTO(
            email: $user->getEmail(),
            publicId: $user->getPublicId(),
            username: $user->getDisplayName(),
            locale: $user->getLocale(),
            timezone: $user->getTimezone(),
            roles: $user->getRoles()
        );

    }
}
<?php

namespace App\Service;

use App\DTO\User\Output\UserAttributesOutputDTO;
use App\DTO\User\Output\UserCollectionAttributesOutputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;

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
            id: $user->getId(),
            publicId: $user->getPublicId(),
            email: $user->getEmail(),
            username: $user->getDisplayName(),
            locale: $user->getLocale(),
            timezone: $user->getTimezone(),
            roles: $user->getRoles(),
            avatarPath: $user->getAvatarPath(),
            emailVerifiedAt: $user->getEmailVerifiedAt(),
            isActive: $user->isActive(),
            lastLoginAt: $user->getLastLoginAt(),
            createdAt: $user->getCreatedAt(),
            updatedAt: $user->getUpdatedAt(),
            deletedAt: $user->getDeletedAt(),
        );

    }

    public function toCollectionAttributesForUser(array $users): UserCollectionAttributesOutputDTO
    {
        dump("users dans toCollectionAttributesForUser");
        dump($users);
        //return all users as UserCollectionAttributesOutputDTO array through UserAttributesOutputDTO
        $userDTOs = [];
        //boucle et appel la methode toDetailsAttributesForUser
        foreach ($users as $user) {
            $userDTOs[] = $this->toDetailsAttributesForUser($user);
        }
        return new UserCollectionAttributesOutputDTO(
            users: $userDTOs
        );

    }

    public function findOneByPublicId(Uuid $publicId): ?User
    {
        return $this->userRepository->findOneBy(['publicId' => $publicId]);
    }

    public function findAll(): array
    {
        return $this->userRepository->findAll();
    }
}
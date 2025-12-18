<?php

namespace App\Service;

use App\DTO\RegistrationUser\Input\UserRegistrationInputDTO;
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

    
    public function createUser(UserRegistrationInputDTO $data, string $hashedPassword): User
    {
        $user = new User();
        $user->setEmail($data->email);
        $user->setUsername($data->username);
        $user->setPassword($hashedPassword);
        $user->setTimezone($data->timezone);
        $user->setLocale($data->locale);
        $user->setRoles($data->roles);
        
        $this->userRepository->save($user, true);
        
        return $user;
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
        $userDTOs = [];
        
        foreach ($users as $user) {
            $userDTOs[] = $this->toDetailsAttributesForUser($user);
        }
        return new UserCollectionAttributesOutputDTO(
            users: $userDTOs
        );
        
    }
    
    public function findOneById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
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
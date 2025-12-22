<?php

namespace App\Service;

use App\DTO\RegistrationUser\Input\UserRegistrationInputDTO;
use App\DTO\User\Input\UserUpdateInputDTO;
use App\DTO\User\Output\UserAttributesOutputDTO;
use App\DTO\User\Output\UserCollectionAttributesOutputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,

    ) {}

    
    public function createNewUser(UserRegistrationInputDTO $data): User
    {
        $user = new User();
        $user->setEmail($data->email);
        $user->setUsername($data->username);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data->password);
        $user->setPassword($hashedPassword);
        $user->setTimezone($data->timezone);
        $user->setLocale($data->locale);
        $user->setRoles($data->roles);
        
        $this->userRepository->save($user, true);
        
        return $user;
    }

    public function updateUser(UserUpdateInputDTO $data, User $user): User
    {
        if ($data->username !== null) {
            $user->setUsername($data->username);
        }
        if ($data->password !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data->password);
            $user->setPassword($hashedPassword);
        }
        if ($data->is_active !== null) {
            $user->setIsActive($data->is_active);
        }
        if ($data->roles !== null) {
            $user->setRoles($data->roles);
        }
        if ($data->email_verified_at !== null) {
            $user->setEmailVerifiedAt($data->email_verified_at);
        }
        
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

    public function toCollectionAttributesForUsers(array $users): UserCollectionAttributesOutputDTO
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
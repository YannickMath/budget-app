<?php

namespace App\Service;

use App\DTO\RegistrationUser\Input\UserRegistrationInputDTO;
use App\DTO\User\Input\UserUpdateInputDTO;
use App\DTO\User\Output\UserAttributesOutputDTO;
use App\DTO\User\Output\UserCollectionAttributesOutputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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

        try {
            $this->userRepository->save($user, true);
        } catch (UniqueConstraintViolationException $e) {
            throw new UnprocessableEntityHttpException('Cet email ou nom d\'utilisateur est déjà utilisé');
        } catch (Exception $e) {
            throw new RuntimeException('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
        }

        return $user;
    }

    public function updateUser(UserUpdateInputDTO $data, User $user): User
    {
        $hasChanges = false;
        
        if ($data->username !== null && $data->username !== $user->getDisplayName()) {
            $user->setUsername($data->username);
            $hasChanges = true;
        }
        if ($data->password !== null && !password_verify($data->password, $user->getPassword())) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data->password);
            $user->setPassword($hashedPassword);
            $hasChanges = true;
        }
        if ($data->is_active !== null && $data->is_active !== $user->isActive()) {
            $user->setIsActive($data->is_active);
            $hasChanges = true;     
        }
        if ($data->roles !== null && $data->roles !== $user->getRoles()) {
            $user->setRoles($data->roles);
            $hasChanges = true;
        }
        if ($data->email_verified_at !== null && $data->email_verified_at !== $user->getEmailVerifiedAt()) {
            $user->setEmailVerifiedAt($data->email_verified_at);
            $hasChanges = true;
        }
        
        if (!$hasChanges) {
            throw new UnprocessableEntityHttpException('No changes were made to the user.');
        }

        try {
            $this->userRepository->save($user, true);
        } catch (UniqueConstraintViolationException $e) {
            throw new UnprocessableEntityHttpException('Cet email ou nom d\'utilisateur est déjà utilisé');
        } catch (Exception $e) {
            throw new RuntimeException('Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage());
        }

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
<?php

namespace App\Service\User;

use App\DTO\User\Output\UserAttributesOutputDTO;
use App\DTO\User\Output\UserCollectionAttributesOutputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

/**
 * UserService provides reusable utility methods for user operations
 * Used as a base by ProfileService, AdminUserService, AuthService, etc.
 *
 * This service contains only:
 * - Read operations (find, search)
 * - Utility methods (transformations, validations)
 * - Low-level operations used by other services
 */
class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    // ==================== Read Methods ====================
    
    /**
     * Find a user by ID
     */
    public function findOneById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * Find a user by email
     */
    public function findOneByEmail(string $email): ?User
    {
        // Normalize email to lowercase for case-insensitive search
        return $this->userRepository->findOneBy(['email' => strtolower(trim($email))]);
    }

    /**
     * Find a user by username
     */
    public function findOneByUsername(string $username): ?User
    {
        return $this->userRepository->findOneBy(['username' => $username]);
    }

    /**
     * Find a user by public ID
     */
    public function findOneByPublicId(Uuid $publicId): ?User
    {
        return $this->userRepository->findOneBy(['publicId' => $publicId]);
    }

    /**
     * Get all users
     */
    public function findAll(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * Get active users only
     */
    public function findActiveUsers(): array
    {
        return $this->userRepository->findBy(['isActive' => true, 'deletedAt' => null]);
    }

    /**
     * Get deleted users
     */
    public function findDeletedUsers(): array
    {
        return $this->userRepository->createQueryBuilder('u')
            ->where('u.deletedAt IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    // ==================== Validation Methods ====================

    /**
     * Check if an email is available (not already in use)
     */
    public function isEmailAvailable(string $email, ?int $excludeUserId = null): bool
    {
        $user = $this->findOneByEmail($email);

        if (!$user) {
            return true;
        }

        // If excluding a user (for updates), check if it's the same user
        if ($excludeUserId !== null && $user->getId() === $excludeUserId) {
            return true;
        }

        return false;
    }

    /**
     * Check if a username is available (not already in use)
     */
    public function isUsernameAvailable(string $username, ?int $excludeUserId = null): bool
    {
        $user = $this->findOneByUsername($username);

        if (!$user) {
            return true;
        }

        // If excluding a user (for updates), check if it's the same user
        if ($excludeUserId !== null && $user->getId() === $excludeUserId) {
            return true;
        }

        return false;
    }

    /**
     * Verify a plain password against a user's hashed password
     */
    public function verifyPassword(User $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }

    /**
     * Hash a password for a user
     */
    public function hashPassword(User $user, string $plainPassword): string
    {
        return $this->passwordHasher->hashPassword($user, $plainPassword);
    }

    // ==================== Transformation Methods ====================

    /**
     * Transform a User entity to UserAttributesOutputDTO
     */
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

    /**
     * Transform an array of User entities to UserCollectionAttributesOutputDTO
     */
    public function toCollectionAttributesForUsers(array $users): UserCollectionAttributesOutputDTO
    {
        $userDTOs = array_map(
            fn(User $user) => $this->toDetailsAttributesForUser($user),
            $users
        );

        return new UserCollectionAttributesOutputDTO(
            users: $userDTOs
        );
    }

}
<?php

namespace App\DTO\Auth\Output;

use App\Entity\User;

/**
 * Data Transfer Object for user data in auth responses
 * Used for register and login endpoints
 */
readonly class UserOutputDTO
{
    public function __construct(
        public string $username,
        public string $email,
        public bool $emailVerified,
        public string $locale,
    ) {}

    /**
     * Create DTO from User entity
     */
    public static function fromEntity(User $user): self
    {
        return new self(
            username: $user->getDisplayName(),
            email: $user->getEmail(),
            emailVerified: $user->getEmailVerifiedAt() !== null,
            locale: $user->getLocale(),
        );
    }
}

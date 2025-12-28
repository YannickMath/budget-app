<?php

namespace App\DTO\Profile\Output;

/**
 * Data Transfer Object for user profile attributes output
 */
readonly class ProfileAttributesOutputDTO
{
    public function __construct(
        public string $username,
        public string $email,
        public ?string $avatarPath,
        public string $locale,
        public string $timezone,
        public bool $isActive,
    ) {}
}
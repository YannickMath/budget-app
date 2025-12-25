<?php

namespace App\DTO\Profile\Output;

readonly class ProfileAttributesOutputDTO
{
    public function __construct(
        public string $username,
        public ?string $avatarPath,
        public string $locale,
        public string $timezone,
        public bool $isActive,
    ) {}
}
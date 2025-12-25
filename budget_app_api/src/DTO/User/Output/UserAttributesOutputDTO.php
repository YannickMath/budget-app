<?php

namespace App\DTO\User\Output;

use Symfony\Component\Uid\Uuid;

readonly class UserAttributesOutputDTO
{ 
    public function __construct(
        public int $id,
        public Uuid $publicId,
        public string $email,
        public string $username,
        public string $locale,
        public string $timezone,
        public array $roles,
        public ?string $avatarPath,
        public ?\DateTimeImmutable $emailVerifiedAt,
        public bool $isActive,
        public ?\DateTimeImmutable $lastLoginAt,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
        public ?\DateTimeImmutable $deletedAt,
    ) {}
}
<?php

namespace App\DTO\User\Output;

use Symfony\Component\Uid\Uuid;

readonly class UserAttributesOutputDTO
{
    public function __construct(
        public string $email,
        public Uuid $publicId,
        public string $username,
        public string $locale,
        public string $timezone,
        public array $roles
    ) {}
    public function getPublicId(): Uuid
    {
        return $this->publicId;
    }
}
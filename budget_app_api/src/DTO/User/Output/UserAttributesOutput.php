<?php

namespace App\DTO\User\Output;

readonly class UserAttributesOutput
{
    public function __construct(
        public string $email,
        public string $username,
        public string $locale,
        public string $timezone,
        public array $roles
    ) {}
}
<?php

namespace App\DTO\Auth\Output;

/**
 * Data Transfer Object for authentication responses
 * Used for register and login endpoints
 */
readonly class AuthResponseOutputDTO
{
    public function __construct(
        public string $token,
        public UserOutputDTO $user,
    ) {}
}

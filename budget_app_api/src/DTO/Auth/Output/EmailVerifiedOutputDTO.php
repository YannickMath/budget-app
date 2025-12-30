<?php

namespace App\DTO\Auth\Output;

/**
 * Data Transfer Object for email verification response
 * Used when user verifies their email address
 */
readonly class EmailVerifiedOutputDTO
{
    public function __construct(
        public string $message,
        public bool $emailVerified,
    ) {}
}

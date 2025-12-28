<?php

namespace App\DTO\Profile\Output;

/**
 * Data Transfer Object for email change confirmation response
 * Used when user confirms their new email address
 */
readonly class EmailChangedOutputDTO
{
    public function __construct(
        public string $message,
        public string $email,
    ) {}
}

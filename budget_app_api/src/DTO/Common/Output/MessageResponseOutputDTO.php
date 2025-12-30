<?php

namespace App\DTO\Common\Output;

/**
 * Generic Data Transfer Object for simple message responses
 * Reusable across all endpoints that only return a message
 */
readonly class MessageResponseOutputDTO
{
    public function __construct(
        public string $message,
    ) {}
}

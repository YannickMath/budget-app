<?php

namespace App\Enum;

/**
 * User role constants
 */
final class UserRole
{
    /**
     * Default user role
     */
    public const USER = 'ROLE_USER';

    /**
     * Administrator role
     */
    public const ADMIN = 'ROLE_ADMIN';

    /**
     * Role validation regex pattern
     */
    public const VALIDATION_PATTERN = '/^ROLE_[A-Z_]+$/';

    /**
     * Get all available roles
     *
     * @return array<string>
     */
    public static function getAllRoles(): array
    {
        return [
            self::USER,
            self::ADMIN,
        ];
    }

    /**
     * Prevent instantiation
     */
    private function __construct()
    {
    }
}

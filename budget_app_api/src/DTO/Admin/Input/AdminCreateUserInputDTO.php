<?php

namespace App\DTO\Admin\Input;

use App\Config\AppConfig;
use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for admin user creation
 * Different from public registration - allows setting roles, verified status, etc.
 */
final class AdminCreateUserInputDTO
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Invalid email address')]
    #[Assert\Length(max: 180)]
    public string $email;

    #[Assert\NotBlank(message: 'Username is required')]
    #[Assert\Length(min: 3, max: 50)]
    public string $username;

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(min: 8, max: 30)]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Timezone]
    public string $timezone = AppConfig::DEFAULT_TIMEZONE;

    #[Assert\Choice(choices: AppConfig::AVAILABLE_LOCALES)]
    public string $locale = AppConfig::DEFAULT_LOCALE;

    #[Assert\NotBlank]
    public array $roles = [UserRole::USER];

    /**
     * Admin can create users with pre-verified email
     */
    public bool $emailVerified = false;

    /**
     * Admin can create active or inactive users
     */
    public bool $isActive = true;

    public ?string $avatarPath = null;
}

<?php

namespace App\DTO\Auth\Input;

use App\Config\AppConfig;
use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(
    fields: ['email'],
    message: 'This email is already registered.',
    entityClass: User::class
)]
#[UniqueEntity(
    fields: ['username'],
    message: 'This username is already taken.',
    entityClass: User::class
)]
/**
 * Data Transfer Object for user registration input
 */
final class RegisterInputDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 30)]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Timezone]
    public string $timezone = AppConfig::DEFAULT_TIMEZONE;

    #[Assert\Choice(choices: AppConfig::AVAILABLE_LOCALES)]
    public string $locale = AppConfig::DEFAULT_LOCALE;

    #[Assert\NotBlank()]
    public array $roles = [UserRole::USER];
}
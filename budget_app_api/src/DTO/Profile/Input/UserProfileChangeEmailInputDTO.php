<?php

namespace App\DTO\Profile\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for user profile change email input
 */
final class UserProfileChangeEmailInputDTO
{
    #[Assert\NotBlank(message: 'New email must not be blank.')]
    #[Assert\Email(message: 'The email "{{ value }}" is not a valid email address.')]
    #[Assert\Length(max: 180)]
    public ?string $newEmail = null;

    #[Assert\NotBlank(message: 'Email confirmation must not be blank.')]
    #[Assert\EqualTo(
        propertyPath: 'newEmail',
        message: 'Email addresses do not match.'
    )]
    public ?string $newEmailConfirmation = null;

    #[Assert\NotBlank(message: 'Password must not be blank.')]
    public ?string $password = null;
}
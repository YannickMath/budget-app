<?php

namespace App\DTO\Auth\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for reset password input
 */
final class ResetPasswordInputDTO
{
    #[Assert\NotBlank(message: 'Password must not be blank.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Password must be at least {{ limit }} characters long.',
        max: 30,
        maxMessage: 'Password cannot be longer than {{ limit }} characters.'
    )]
    public ?string $password;

    #[Assert\NotBlank(message: 'Password confirmation must not be blank.')]
    #[Assert\EqualTo(
        propertyPath: 'password',
        message: 'Passwords do not match.'
    )]
    public ?string $passwordConfirmation;
}
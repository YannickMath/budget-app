<?php

namespace App\DTO\Profile\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for user profile password change input
 */
final class UserProfileChangePasswordInputDTO
{
    #[Assert\NotBlank(message: 'Current password should not be blank')]
    public string $currentPassword;

    #[Assert\NotBlank(message: 'New password should not be blank')]
    #[Assert\Length(
        min: 8,
        max: 30,
        minMessage: 'New password must be at least {{ limit }} characters long',
        maxMessage: 'New password cannot be longer than {{ limit }} characters'
    )]
    public string $newPassword;

    #[Assert\NotBlank(message: 'Please confirm your new password')]
    #[Assert\EqualTo(
        propertyPath: 'newPassword',
        message: 'New password and confirmation do not match'
    )]
    public string $confirmNewPassword;
}
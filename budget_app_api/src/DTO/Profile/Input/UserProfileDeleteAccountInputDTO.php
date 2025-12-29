<?php

namespace App\DTO\Profile\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for user profile delete account input
 */
final class UserProfileDeleteAccountInputDTO
{
    #[Assert\NotBlank(message: "Password is required to confirm account deletion")]
    public string $password;

    #[Assert\Length(max: 500, maxMessage: "Deletion reason cannot exceed {{ limit }} characters")]
    public ?string $reason = null;
}
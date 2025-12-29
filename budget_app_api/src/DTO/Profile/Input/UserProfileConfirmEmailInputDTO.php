<?php

namespace App\DTO\Profile\Input;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for user profile email confirmation input
 */
final class UserProfileConfirmEmailInputDTO
{
    #[Assert\NotBlank()]
    #[Assert\Length(min: 32, max: 64)]
    public ?string $token = null;
}
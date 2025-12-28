<?php

namespace App\DTO\Auth\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for reset password input
 */
class ResetPasswordInputDTO
{
    #[Assert\NotBlank(message: 'Le nouveau mot de passe ne doit pas être vide.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères',
        max: 30,
        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères'
    )]
    public ?string $password;
}
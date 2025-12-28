<?php

namespace App\DTO\Auth\Input;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for forgot password input
 */
class ForgotPasswordInputDTO
{
    #[Email(message: 'L\'email {{ value }} n\'est pas un email valide.')]
    #[Assert\NotBlank(message: 'L\'email ne doit pas être vide.')]
    public ?string $email;
}
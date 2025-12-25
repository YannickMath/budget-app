<?php

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;

class ForgotPasswordInputDTO
{
    #[Email(message: 'L\'email {{ value }} n\'est pas un email valide.')]
    #[Assert\NotBlank(message: 'L\'email ne doit pas être vide.')]
    public ?string $email;
}
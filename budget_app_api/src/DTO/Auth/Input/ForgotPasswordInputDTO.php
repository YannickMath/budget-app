<?php

namespace App\DTO\Auth\Input;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for forgot password input
 */
final class ForgotPasswordInputDTO
{
    #[Assert\NotBlank(message: 'Email must not be blank.')]
    #[Email(message: 'The email "{{ value }}" is not a valid email address.')]
    #[Assert\Length(max: 180)]
    public ?string $email;
}
<?php

namespace App\DTO\Admin\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for admin password reset
 */
readonly class ResetUserPasswordInputDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Password must not be blank.')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Password must be at least {{ limit }} characters long.'
        )]
        public string $password,
    ) {}
}

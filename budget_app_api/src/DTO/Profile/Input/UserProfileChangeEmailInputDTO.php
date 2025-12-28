<?php

namespace App\DTO\Profile\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for user profile change email input
 */
class UserProfileChangeEmailInputDTO
{
    #[Assert\NotBlank()]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public  ?string $new_email = null;

    #[Assert\NotBlank()]
    #[Assert\Length(min: 3, max: 50)]
    public  ?string $password = null;
}
<?php

namespace App\DTO\Profile\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for user profile edit input
 * Note: Email changes must use the dedicated changeEmail endpoint with confirmation
 */
class UserProfileEditInputDTO
{
    #[Assert\Length(min: 3, max: 50)]
    public  ?string $username = null;

    public  ?string $timezone = null;

    public  ?string $locale = null;

    public ?string $avatarPath = null;
}
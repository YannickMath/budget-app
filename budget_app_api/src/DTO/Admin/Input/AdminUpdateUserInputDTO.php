<?php

namespace App\DTO\Admin\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for admin user updates
 * Allows updating any user field including roles and verification status
 */
class AdminUpdateUserInputDTO
{
    #[Assert\Email(message: 'Invalid email address')]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\Length(min: 3, max: 50)]
    public ?string $username = null;

    #[Assert\Length(min: 8, max: 30)]
    public ?string $password = null;

    #[Assert\Timezone]
    public ?string $timezone = null;

    #[Assert\Choice(choices: ['fr', 'en'])]
    public ?string $locale = null;

    public ?array $roles = null;

    public ?bool $isActive = null;

    public ?string $avatarPath = null;

    /**
     * Admin can manually verify/unverify email
     * Set to current timestamp to verify, null to unverify
     */
    public ?bool $emailVerified = null;
}

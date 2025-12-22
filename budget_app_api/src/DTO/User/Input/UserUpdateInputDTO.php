<?php

namespace App\DTO\User\Input;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateInputDTO
{       
    #[Assert\Length(min: 3, max: 50)]
    public ?string $username = null;
    
    #[Assert\Length(min: 8, max: 30)]
    public ?string $password = null;
    
    public ?bool $is_active = null;
    
    public ?array $roles = null;
    
    #[Assert\DateTime]
    public ?\DateTimeInterface $email_verified_at = null;
}
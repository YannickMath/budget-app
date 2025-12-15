<?php

namespace App\DTO\User\Input;

use Symfony\Component\Validator\Constraints as Assert;

class UserRegistrationInput
{       
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180, unique: true)]
    public ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50, unique: true)]
    public ?string $username = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 30)]
    public ?string $password = null;
    
    #[Assert\NotBlank]
    #[Assert\Timezone]
    public string $timezone = 'Europe/Paris';
        
    #[Assert\Choice(choices: ['fr', 'en'])]
    public string $locale = 'fr';

}
<?php

namespace App\DTO\User\Input;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(
    fields: ['email'],
    message: 'This email is already registered.',
    entityClass: User::class
)]
#[UniqueEntity(
    fields: ['username'],
    message: 'This username is already taken.',
    entityClass: User::class
)]
class UserAttributesInputDTO
{       
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 30)]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Timezone]
    public string $timezone = 'Europe/Paris';
        
    #[Assert\Choice(choices: ['fr', 'en'])]
    public string $locale = 'fr';

    #[Assert\NotBlank()]
    public array $roles = ['ROLE_USER'];
}
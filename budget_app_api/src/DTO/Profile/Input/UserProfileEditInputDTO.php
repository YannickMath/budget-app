<?php

namespace App\DTO\Profile\Input;

use App\Entity\User;
use DateTimeInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Date;

#[UniqueEntity(
    fields: ['email'],
    message: 'This email is already registered.',
    entityClass: User::class
)   ]

class UserProfileEditInputDTO
{
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public  ?string $email = null;

    #[Assert\Length(min: 3, max: 50)]
    public  ?string $username = null;

    public  ?string $timezone = null;

    public  ?string $locale = null;

    public ?string $avatarPath = null;
}
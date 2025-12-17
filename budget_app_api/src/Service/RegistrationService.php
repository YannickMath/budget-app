<?php

namespace App\Service;

use App\DTO\RegistrationUser\Input\UserRegistrationInputDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function registerNewUser(UserRegistrationInputDTO $input): User
    {
        $existingUserByEmail = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $input->email]);
        
        if ($existingUserByEmail) {throw new \Exception('Cet email est déjà utilisé');}
        
        $existingUserByUsername = $this->entityManager->getRepository(User::class)
            ->findOneBy(['username' => $input->username]);
        
        if ($existingUserByUsername) {throw new \Exception('Ce nom d\'utilisateur est déjà utilisé');}

        $user = new User();
        $user->setEmail($input->email);
        $user->setUsername($input->username);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $input->password);
        $user->setPassword($hashedPassword);
        $user->setRoles($input->roles);
        $user->setTimezone($input->timezone);
        $user->setLocale($input->locale);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;

    }
}
<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-user',
    description: 'Crée un utilisateur de test',
)]
class CreateTestUserCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = 'test@example.com';

        $existingUser = $this->userRepository->findOneBy(['email' => $email]);

        if ($existingUser) {
            $io->warning("L'utilisateur {$email} existe déjà !");
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername('testuser');
        $user->setTimezone('Europe/Paris');
        $user->setLocale('fr');
        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(true);
        $user->setEmailVerifiedAt(new \DateTimeImmutable()); // Email vérifié pour éviter le blocage

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user, true);

        $io->success(sprintf(
            'Utilisateur de test créé avec succès !' . PHP_EOL .
            'Email: %s' . PHP_EOL .
            'Password: password123' . PHP_EOL .
            'ID: %d',
            $user->getEmail(),
            $user->getId()
        ));

        return Command::SUCCESS;
    }
}
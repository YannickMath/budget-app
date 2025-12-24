<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Event\ForgotPasswordEvent;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ForgotPasswordService
{
    public function __construct(
        private UserRepository $userRepository,
        private EventDispatcherInterface $eventDispatcher,

    ){}

    public function requestPasswordReset(string $email): void
    {
        
        $user = $this->userRepository->findOneByEmail($email);
        if (!$user) {
            return;
        }

        $this->generatePasswordResetToken($user);

    }

    public function generatePasswordResetToken(User $user): void
    {
        $generatedToken = bin2hex(random_bytes(32));
        $expirationTime = new \DateTimeImmutable('+1 hour');

        $user->setPasswordResetToken($generatedToken);
        $user->setPasswordResetTokenExpiresAt($expirationTime);
        $this->userRepository->save($user, true);

        $event = new ForgotPasswordEvent($user);
        $this->eventDispatcher->dispatch($event);
    }

}
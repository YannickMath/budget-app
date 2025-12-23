<?php

namespace App\Service;

use App\Repository\UserRepository;

class EmailVerificationService
{
    // Implementation of email verification logic goes here
    public function __construct(
        private UserRepository $userRepository
    ) {}


    public function generateVerificationToken($user)
    {
        $token = bin2hex(random_bytes(16));
        $expiresAt = new \DateTimeImmutable('+1 day');

        $user->setEmailVerificationToken($token);
        $user->setEmailVerificationTokenExpiresAt($expiresAt);
        $this->userRepository->save($user, true);
    }

    public function verifyToken(string $token): bool
    {
        $user = $this->userRepository->findOneBy(['email_verification_token' => $token]);

        if (!$user) {
            return false;
        }

        if ($user->getEmailVerifiedAt() !== null) {
            return true;
        }

        if ($user->getEmailVerificationTokenExpiresAt() < new \DateTimeImmutable()) {
            return false;
        }

        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationTokenExpiresAt(null);
        $this->userRepository->save($user, true);

        return true;
    }
    
}
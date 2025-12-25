<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EmailVerificationService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function generateVerificationToken(User $user): void
    {
        $token = bin2hex(random_bytes(16));
        $expiresAt = new \DateTimeImmutable('+1 day');

        $user->setEmailVerificationToken($token);
        $user->setEmailVerificationTokenExpiresAt($expiresAt);
        $this->userRepository->save($user, true);
    }

    public function verifyToken(string $token): void
    {
        $user = $this->userRepository->findOneBy(['email_verification_token' => $token]);

        if (!$user) {
            throw new BadRequestHttpException('Invalid or expired token.');
        }

        if ($user->getEmailVerifiedAt() !== null) {
            return;
        }

        if (!$user->isEmailVerificationTokenValid()) {
            throw new BadRequestHttpException('Invalid or expired token.');
        }

        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationTokenExpiresAt(null);
        $this->userRepository->save($user, true);
    }
}
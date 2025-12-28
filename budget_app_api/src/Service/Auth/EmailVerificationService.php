<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Service to manage email verification operations
 */
class EmailVerificationService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    /**
     * Generate and assign a verification token to the user
     */
    public function generateVerificationToken(User $user): void
    {
        $token = bin2hex(random_bytes(16));
        $expiresAt = new \DateTimeImmutable('+1 day');

        $user->setEmailVerificationToken($token);
        $user->setEmailVerificationTokenExpiresAt($expiresAt);

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate email verification token: ' . $e->getMessage());
        }
    }

    /**
     * Verify the email using the provided token
     */
    public function verifyToken(string $token): void
    {
        $user = $this->userRepository->findOneBy(['emailVerificationToken' => $token]);

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

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to verify email: ' . $e->getMessage());
        }
    }
}
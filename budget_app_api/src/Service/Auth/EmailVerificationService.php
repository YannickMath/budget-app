<?php

namespace App\Service\Auth;

use App\DTO\Auth\Output\EmailVerifiedOutputDTO;
use App\DTO\Common\Output\MessageResponseOutputDTO;
use App\Entity\User;
use App\Event\RegisterSuccessEvent;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Service to manage email verification operations
 */
class EmailVerificationService
{
    public function __construct(
        private UserRepository $userRepository,
        private EventDispatcherInterface $dispatcher
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
    public function verifyToken(string $token): EmailVerifiedOutputDTO
    {
        $user = $this->userRepository->findOneBy(['emailVerificationToken' => $token]);

        if (!$user) {
            throw new BadRequestHttpException('Invalid or expired token.');
        }

        if ($user->getEmailVerifiedAt() !== null) {
            return new EmailVerifiedOutputDTO(
                message: 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.',
                emailVerified: true
            );
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

        return new EmailVerifiedOutputDTO(
            message: 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.',
            emailVerified: true
        );
    }

    /**
     * Resend verification email for a user
     */
    public function resendVerificationEmail(User $user): MessageResponseOutputDTO
    {
        if ($user->getEmailVerifiedAt() !== null) {
            throw new BadRequestHttpException('Email is already verified.');
        }

        if ($user->getEmailVerificationToken() && $user->isEmailVerificationTokenValid()) {
            throw new BadRequestHttpException('A verification email was already sent. Please check your inbox or wait before requesting a new one.');
        }

        $this->generateVerificationToken($user);

        $event = new RegisterSuccessEvent($user);
        $this->dispatcher->dispatch($event);

        return new MessageResponseOutputDTO(
            message: 'Verification email has been resent. Please check your inbox.'
        );
    }
}
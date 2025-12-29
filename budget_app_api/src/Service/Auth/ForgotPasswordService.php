<?php

namespace App\Service\Auth;

use App\DTO\Common\Output\MessageResponseOutputDTO;
use App\Entity\User;
use App\Event\ForgotPasswordEvent;
use App\Repository\UserRepository;
use App\Service\User\UserService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Service to manage forgot password operations
 */
class ForgotPasswordService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService,
        private EventDispatcherInterface $eventDispatcher,

    ){}

    /**
     * Request a password reset for the given email
     */
    public function requestPasswordReset(string $email): MessageResponseOutputDTO
    {

        $user = $this->userService->findOneByEmail($email);
        if (!$user || !$user->isActive()) {
            return new MessageResponseOutputDTO(
                message: "If the email exists, a reset link has been sent."
            );
        }

        $this->generatePasswordResetToken($user);

        return new MessageResponseOutputDTO(
            message: "If the email exists, a reset link has been sent."
        );
    }

    /**
     * Generate a password reset token and dispatch event to send email
     */
    public function generatePasswordResetToken(User $user): void
    {
        $generatedToken = bin2hex(random_bytes(32));
        $expirationTime = new \DateTimeImmutable('+1 hour');

        $user->setPasswordResetToken($generatedToken);
        $user->setPasswordResetTokenExpiresAt($expirationTime);

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate password reset token: ' . $e->getMessage());
        }

        $event = new ForgotPasswordEvent($user);
        $this->eventDispatcher->dispatch($event);
    }

}
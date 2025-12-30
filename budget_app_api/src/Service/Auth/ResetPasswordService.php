<?php

namespace App\Service\Auth;

use App\DTO\Auth\Input\ResetPasswordInputDTO;
use App\DTO\Common\Output\MessageResponseOutputDTO;
use App\Repository\UserRepository;
use App\Service\User\UserService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Service to manage password reset operations
 */
class ResetPasswordService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService
    ){}

    /**
     * Validate if the provided token is valid
     */
    public function validateToken(string $token): bool
    {
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);

        return $user && $user->isPasswordResetTokenValid();
    }

    /**
     * Reset the user's password using the provided token and input data
     */
    public function resetPassword(string $token, ResetPasswordInputDTO $input): MessageResponseOutputDTO
    {
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);

        if (!$user || !$user->isPasswordResetTokenValid() || !$user->isActive()) {
            throw new BadRequestHttpException('Invalid or expired token.');
        }

        $hashedPassword = $this->userService->hashPassword($user, $input->password);
        $user->setPassword($hashedPassword);
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenExpiresAt(null);

        try{
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to reset password: ' . $e->getMessage());
        }

        return new MessageResponseOutputDTO(
            message: 'Password has been reset successfully.'
        );
    }
}
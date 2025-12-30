<?php

namespace App\Service\Auth;

use App\DTO\Auth\Input\ResetPasswordInputDTO;
use App\DTO\Auth\Input\RegisterInputDTO;
use App\DTO\Auth\Output\EmailVerifiedOutputDTO;
use App\DTO\Common\Output\MessageResponseOutputDTO;
use App\Entity\User;

/**
 * AuthService acts as a facade for all authentication-related operations.
 * It delegates to specialized services while providing a single entry point.
 */
class AuthService
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly EmailVerificationService $emailVerificationService,
        private readonly ForgotPasswordService $forgotPasswordService,
        private readonly ResetPasswordService $resetPasswordService,
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterInputDTO $input): User
    {
        return $this->registrationService->registerNewUser($input);
    }

    /**
     * Generate email verification token for a user
     */
    public function generateEmailVerificationToken(User $user): void
    {
        $this->emailVerificationService->generateVerificationToken($user);
    }

    /**
     * Verify user's email with token
     */
    public function verifyEmail(string $token): EmailVerifiedOutputDTO
    {
        return $this->emailVerificationService->verifyToken($token);
    }

    /**
     * Request a password reset (sends email with token)
     */
    public function requestPasswordReset(string $email): MessageResponseOutputDTO
    {
        return $this->forgotPasswordService->requestPasswordReset($email);
    }

    /**
     * Validate if a password reset token is valid
     */
    public function validatePasswordResetToken(string $token): bool
    {
        return $this->resetPasswordService->validateToken($token);
    }

    /**
     * Reset password using token
     */
    public function resetPassword(string $token, ResetPasswordInputDTO $input): MessageResponseOutputDTO
    {
        return $this->resetPasswordService->resetPassword($token, $input);
    }
}

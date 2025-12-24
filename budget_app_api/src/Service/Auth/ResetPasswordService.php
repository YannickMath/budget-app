<?php

namespace App\Service\Auth;

use App\DTO\Auth\ResetPasswordInputDTO;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ){}

    public function resetPassword(string $token, ResetPasswordInputDTO $input): void
    {
        $user = $this->userRepository->findOneBy(['password_reset_token' => $token]);

        if (!$user || !$user->isPasswordResetTokenValid()) {
            throw new BadRequestHttpException('Invalid or expired token.');
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $input->password);
        $user->setPassword($hashedPassword);
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenExpiresAt(null);
        $this->userRepository->save($user, true);
    }
}
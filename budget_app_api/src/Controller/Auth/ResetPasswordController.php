<?php

namespace App\Controller\Auth;

use App\DTO\Auth\ResetPasswordInputDTO;
use App\Service\Auth\ResetPasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordService $resetPasswordService
    ) {}

    #[Route('/api/auth/reset-password', name: 'app_auth_reset_password', methods: ['POST'])]
    public function resetPassword(
        #[MapQueryParameter] string $token,
        #[MapRequestPayload] ResetPasswordInputDTO $input
    ): JsonResponse
    {
        $this->resetPasswordService->resetPassword($token, $input);

        return $this->json(['message' => 'Le mot de passe a été réinitialisé avec succès.'], Response::HTTP_OK);
    }
}
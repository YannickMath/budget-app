<?php

namespace App\Controller\Auth;

use App\DTO\Auth\ResetPasswordInputDTO;
use App\Service\Auth\ResetPasswordService;
use App\Trait\RateLimiterTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ResetPasswordController extends AbstractController
{
    use RateLimiterTrait;

    public function __construct(
        private ResetPasswordService $resetPasswordService,
        private RateLimiterFactoryInterface $passwordResetLimiter,
    ) {}

    #[Route('/api/auth/reset-password/validate', name: 'app_auth_validate_reset_token', methods: ['GET'])]
    public function validateResetToken(
        #[MapQueryParameter] string $token,
        Request $request
    ): JsonResponse
    {
        $this->applyRateLimit($this->passwordResetLimiter, $request);

        $isValid = $this->resetPasswordService->validateToken($token);

        if (!$isValid) {
            return $this->json([
                'success' => false,
                'message' => 'Token invalide ou expiré.'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => 'Token valide.'
        ], Response::HTTP_OK);
    }

    #[Route('/api/auth/reset-password', name: 'app_auth_reset_password', methods: ['POST'])]
    public function resetPassword(
        #[MapQueryParameter] string $token,
        #[MapRequestPayload] ResetPasswordInputDTO $input,
        Request $request
    ): JsonResponse
    {
        $this->applyRateLimit($this->passwordResetLimiter, $request);

        $this->resetPasswordService->resetPassword($token, $input);

        return $this->json(['message' => 'Le mot de passe a été réinitialisé avec succès.'], Response::HTTP_OK);
    }
}
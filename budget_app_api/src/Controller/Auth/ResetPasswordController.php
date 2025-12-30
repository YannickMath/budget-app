<?php

namespace App\Controller\Auth;

use App\DTO\Auth\Input\ResetPasswordInputDTO;
use App\Service\Auth\AuthService;
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
        private readonly AuthService $authService,
        private readonly RateLimiterFactoryInterface $passwordResetLimiter,
    ) {}

    #[Route('/api/auth/reset-password/validate', name: 'app_auth_validate_reset_token', methods: ['GET'])]
    public function validateResetToken(
        #[MapQueryParameter] string $token,
        Request $request
    ): JsonResponse
    {
        $this->applyRateLimit($this->passwordResetLimiter, $request);

        $isValid = $this->authService->validatePasswordResetToken($token);

        if (!$isValid) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid or expired token.'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => 'Valid token.'
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

        $response = $this->authService->resetPassword($token, $input);

        return $this->json($response, Response::HTTP_OK);
    }
}
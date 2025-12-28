<?php

namespace App\Controller\Auth;

use App\Service\Auth\AuthService;
use App\Trait\RateLimiterTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class VerifyEmailController extends AbstractController
{
    use RateLimiterTrait;

    public function __construct(
        private readonly AuthService $authService,
        private readonly RateLimiterFactoryInterface $authEndpointLimiter,
    ) {}

    #[Route('/api/auth/verify-email', name: 'app_auth_verify_email', methods: ['GET'])]
    public function verifyEmail(
        #[MapQueryParameter] string $token,
        Request $request
    ): JsonResponse {
        $this->applyRateLimit($this->authEndpointLimiter, $request);

        $this->authService->verifyEmail($token);

        return $this->json([
            'success' => true,
            'message' => 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.'
        ]);
    }
}
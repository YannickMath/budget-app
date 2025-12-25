<?php

namespace App\Controller\Auth;

use App\Service\Auth\EmailVerificationService;
use App\Trait\RateLimiterTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

class VerifyEmailController extends AbstractController
{
    use RateLimiterTrait;

    public function __construct(
        private readonly RateLimiterFactoryInterface $authEndpointLimiter,
    ) {}

    #[Route('/api/auth/verify-email', name: 'app_auth_verify_email', methods: ['GET'])]
    public function verifyEmail(
        #[MapQueryParameter] string $token,
        EmailVerificationService $emailVerificationService,
        Request $request
    ): JsonResponse {
        $this->applyRateLimit($this->authEndpointLimiter, $request);

        $emailVerificationService->verifyToken($token);

        return $this->json([
            'success' => true,
            'message' => 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.'
        ]);
    }
}
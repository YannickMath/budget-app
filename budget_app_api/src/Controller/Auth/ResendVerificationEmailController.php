<?php

namespace App\Controller\Auth;

use App\Service\Auth\EmailVerificationService;
use App\Trait\RateLimiterTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ResendVerificationEmailController extends AbstractController
{
    use RateLimiterTrait;

    public function __construct(
        private readonly RateLimiterFactoryInterface $authEndpointLimiter,
        private readonly EmailVerificationService $emailVerificationService
    ) {}

    #[Route('/api/auth/resend-verification-email', name: 'app_resend_verification_email', methods: ['POST'])]
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $this->applyRateLimit($this->authEndpointLimiter, $request);

        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'User not authenticated.'], 401);
        }

        try {
            $response = $this->emailVerificationService->resendVerificationEmail($user);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }

        return $this->json($response, 200);
    }
}
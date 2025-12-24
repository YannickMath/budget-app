<?php

namespace App\Controller\Auth;

use App\Service\Auth\EmailVerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class VerifyEmailController extends AbstractController
{
    #[Route('/api/auth/verify-email', name: 'app_auth_verify_email', methods: ['GET'])]
    public function verifyEmail(
        #[MapQueryParameter] string $token,
        EmailVerificationService $emailVerificationService
    ): JsonResponse {
        $emailVerificationService->verifyToken($token);

        return $this->json([
            'success' => true,
            'message' => 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.'
        ]);
    }
}
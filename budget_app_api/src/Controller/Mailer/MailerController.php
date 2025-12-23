<?php

namespace App\Controller\Mailer;

use App\Service\EmailVerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailerController extends AbstractController
{
    #[Route('/api/auth/verify-email', name: 'app_verify_email', methods: ['GET'])]
    public function verifyEmail(
        Request $request,
        EmailVerificationService $emailVerificationService
    ): Response {
        $token = $request->query->get('token');

        if (!$token) {
            return $this->json([
                'success' => false,
                'message' => 'Token manquant'
            ], Response::HTTP_BAD_REQUEST);
        }

        $isVerified = $emailVerificationService->verifyToken($token);

        if (!$isVerified) {
            return $this->json([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.'
        ]);
    }
}
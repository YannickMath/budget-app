<?php

namespace App\Controller\Auth;

use App\DTO\Auth\ForgotPasswordInputDTO;
use App\Service\Auth\ForgotPasswordService;
use App\Trait\RateLimiterTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
#[AsController]
final class ForgotPasswordController extends AbstractController
{
    use RateLimiterTrait;

    public function __construct(
        private readonly ForgotPasswordService $forgotPasswordService,
        private readonly RateLimiterFactoryInterface $passwordResetLimiter,
    ) {}

    #[Route('/api/auth/forgot-password', name: 'app_auth_forgot_password', methods: ['POST'])]
    public function forgotPassword(
        #[MapRequestPayload()] ForgotPasswordInputDTO $input,
        Request $request
    ): JsonResponse
    {
        $this->applyRateLimit($this->passwordResetLimiter, $request);

        $this->forgotPasswordService->requestPasswordReset($input->email);

        return $this->json(["message" => "Si l'email existe, un lien de réinitialisation a été envoyé."], 200);
    }

}
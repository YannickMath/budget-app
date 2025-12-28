<?php

namespace App\Controller\Auth;

use App\DTO\Auth\Input\RegisterInputDTO;
use App\Service\Auth\AuthService;
use App\Trait\RateLimiterTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class RegisterController extends AbstractController
{
    use RateLimiterTrait;

    public function __construct(
        private readonly AuthService $authService,
        private readonly RateLimiterFactoryInterface $authEndpointLimiter,
    ) {}

    #[Route('/api/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload()] RegisterInputDTO $input,
        Request $request
    ): JsonResponse
    {
        $this->applyRateLimit($this->authEndpointLimiter, $request);

        $this->authService->register($input);

        return $this->json([
            'message' => 'User registered successfully',
        ], 201);
    }
}
<?php

namespace App\Controller\Auth;

use App\DTO\RegistrationUser\Input\UserRegistrationInputDTO;
use App\Service\Auth\RegistrationService;
use App\Trait\RateLimiterTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class RegisterController extends AbstractController

{
    use RateLimiterTrait;

    public function __construct(
        private RegistrationService $registrationService,
        private RateLimiterFactoryInterface $authEndpointLimiter,
    ) {}

    #[Route('/api/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload()] UserRegistrationInputDTO $input,
        Request $request
    ): JsonResponse
    {
        $this->applyRateLimit($this->authEndpointLimiter, $request);

        $user = $this->registrationService->registerNewUser($input);

        return $this->json([
            'message' => 'User registered successfully',
        ], 201);

    }
}
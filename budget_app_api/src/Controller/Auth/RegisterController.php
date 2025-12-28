<?php

namespace App\Controller\Auth;

use App\DTO\Auth\Input\RegisterInputDTO;
use App\DTO\Auth\Output\AuthResponseOutputDTO;
use App\DTO\Auth\Output\UserOutputDTO;
use App\Service\Auth\AuthService;
use App\Trait\RateLimiterTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
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
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {}

    #[Route('/api/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload()] RegisterInputDTO $input,
        Request $request
    ): JsonResponse
    {
        $this->applyRateLimit($this->authEndpointLimiter, $request);

        $user = $this->authService->register($input);

        $token = $this->jwtManager->create($user);

        $response = new AuthResponseOutputDTO(
            token: $token,
            user: UserOutputDTO::fromEntity($user)
        );

        return $this->json($response, 201);
    }
}
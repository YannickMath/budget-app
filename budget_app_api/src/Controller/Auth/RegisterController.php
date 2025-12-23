<?php

namespace App\Controller\Auth;

use App\DTO\RegistrationUser\Input\UserRegistrationInputDTO;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class RegisterController extends AbstractController

{
    public function __construct(
        private RegistrationService $registrationService,
        private JWTTokenManagerInterface $jwtTokenManager,
        
    ) {}

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(#[MapRequestPayload()] UserRegistrationInputDTO $input): JsonResponse
    ## MapRequestPayload to automatically map the request payload to the DTO by deserializing the JSON payload, manage validation and error handling.
    {
        $user = $this->registrationService->registerNewUser($input);

        return $this->json([
            'message' => 'User registered successfully',
        ], 201);

    }
}
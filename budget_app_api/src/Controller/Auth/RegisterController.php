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
    {
        try {
        $user = $this->registrationService->registerNewUser($input);

        return new JsonResponse(
            [
                'message' => 'Utilisateur enregistré avec succès',
                'userId' => $user->getPublicId(),
                'jwtToken' => $this->jwtTokenManager->create($user),
            ],
            JsonResponse::HTTP_CREATED
        );

        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'error' => $e->getMessage(),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        } catch (\Throwable $e) {
            return new JsonResponse(
                [
                    'error' => 'Une erreur est survenue lors de l\'enregistrement de l\'utilisateur',
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }   

    }
}
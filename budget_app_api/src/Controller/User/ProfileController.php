<?php

namespace App\Controller\User;

use ApiPlatform\Metadata\Exception\AccessDeniedException;
use App\DTO\Profile\Input\UserProfileEditInputDTO;
use App\Service\ProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {} 
    
    #[Route('/api/profile/me', name: 'app_user_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        $user = $this->getUser();

        $profileData = $this->profileService->getProfileData($user);

        return $this->json($profileData);

    }

    #[Route('/api/profile/me/edit', name: 'app_user_profile_edit', methods: ['PUT'])]
    public function editProfile(#[MapRequestPayload()] UserProfileEditInputDTO $input): JsonResponse
    {
        $user = $this->getUser();

        $updatedProfileData = $this->profileService->editProfile($user, $input);

        return $this->json(["message" => "votre profil a été mis à jour", "data" => $updatedProfileData], 200);
    }

    #[Route('/api/profile/me/change-email', name: 'app_user_change_email', methods: ['POST'])]
    public function changeEmail(): JsonResponse
    {
        // Implementation for changing email
        return $this->json(["message" => "Email change functionality not implemented yet."], 501);
    }

    #[Route('/api/profile/me/change-password', name: 'app_user_change_password', methods: ['POST'])]
    public function changePassword(): JsonResponse
    {
        // Implementation for changing password
        return $this->json(["message" => "Password change functionality not implemented yet."], 501);
    }

    #[Route('/api/profile/me/confirmation-email', name: 'app_user_resend_confirmation_email', methods: ['POST'])]
    public function resendConfirmationEmail(): JsonResponse
    {
        // Implementation for resending confirmation email
        return $this->json(["message" => "Resend confirmation email functionality not implemented yet."], 501);
    }
}
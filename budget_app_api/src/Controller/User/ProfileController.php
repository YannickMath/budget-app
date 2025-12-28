<?php

namespace App\Controller\User;

use App\DTO\Profile\Input\UserChangeEmailInputDTO;
use App\DTO\Profile\Input\UserProfileChangeEmailInputDTO;
use App\DTO\Profile\Input\UserProfileChangePasswordInputDTO;
use App\DTO\Profile\Input\UserProfileConfirmEmailInputDTO;
use App\DTO\Profile\Input\UserProfileDeleteAccountInputDTO;
use App\DTO\Profile\Input\UserProfileEditInputDTO;
use App\Entity\User;
use App\Service\User\ProfileService;
use App\Trait\RateLimiterTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ProfileController extends AbstractController
{
    use RateLimiterTrait;
    
    public function __construct(
        private readonly ProfileService $profileService,
        private RateLimiterFactoryInterface $emailChangeLimiter,

    ) {} 
    
    #[Route('/api/profile/me', name: 'app_user_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        $user = $this->getUser();

        try {
            $profileData = $this->profileService->getProfileData($user);
        } catch (\Exception $e) {
            return $this->json(["message" => "Failed to retrieve profile data: " . $e->getMessage()], 400);
        }

        return $this->json($profileData);

    }

    #[Route('/api/profile/me/edit', name: 'app_user_profile_edit', methods: ['PUT'])]
    public function editProfile(#[MapRequestPayload()] UserProfileEditInputDTO $input): JsonResponse
    {
        $user = $this->getUser();
        
        try {
            $updatedProfileData = $this->profileService->editProfile($user, $input);
        } catch (\Exception $e) {
            return $this->json(["message" => "Failed to update profile: " . $e->getMessage()], 400);
        }
        
        return $this->json(["message" => "votre profil a été mis à jour", "data" => $updatedProfileData], 200);
    }

    #[Route('/api/profile/me/change-email', name: 'app_user_change_email', methods: ['POST'])]
    public function changeEmail(#[MapRequestPayload()] UserProfileChangeEmailInputDTO $input, Request $request): JsonResponse
    {
        $this->applyRateLimit($this->emailChangeLimiter, $request);
        
        $user = $this->getUser();

        try {
            $this->profileService->changeEmail($user, $input->new_email);
        } catch (\Exception $e) {
            return $this->json(["message" => "Failed to change email: " . $e->getMessage()], 400);
        }

        return $this->json(["message" => "A confirmation email has been sent to your new email address."], 200);
    }

    #[Route('/api/profile/me/confirmation-new-email', name: 'app_user_resend_confirmation_new_email', methods: ['POST'])]
    public function resendConfirmationEmail(#[MapRequestPayload()] UserProfileConfirmEmailInputDTO $input): JsonResponse
    {
        try {
            $this->profileService->confirmEmailChange($input->token);
        } catch (\Exception $e) {
            return $this->json(["message" => "Failed to resend confirmation email: " . $e->getMessage()], 400);
        }

        return $this->json(["message" => "Your new email has been confirmed.", "data" => $this->profileService->getProfileData($this->getUser())], 200);
    }

    #[Route('/api/profile/me/change-password', name: 'app_user_change_password', methods: ['POST'])]
    public function changePassword(#[MapRequestPayload()] UserProfileChangePasswordInputDTO $input): JsonResponse
    {
        $user = $this->getUser();
        try {
            $this->profileService->changePassword($user, $input);
        } catch (\Exception $e) {
            return $this->json(["message" => "Failed to change password: " . $e->getMessage()], 400);
        }
        return $this->json(["message" => "Your password has been changed successfully. A confirmation email has been sent."], 200);
    }

    #[Route('/api/profile/me/delete-account', name: 'app_user_delete_account', methods: ['DELETE'])]
    public function deleteAccount(#[MapRequestPayload] UserProfileDeleteAccountInputDTO $input): JsonResponse
    {
        $user = $this->getUser();
        try {
            $this->profileService->deleteAccount($user, $input->password, $input->reason);
        } catch (\Exception $e) {
            return $this->json(["message" => "Failed to delete account: " . $e->getMessage()], 400);
        }
        return $this->json(["message" => "Your account has been successfully deleted."], 200);
    }

}
<?php

namespace App\Service;

use App\DTO\Profile\Output\ProfileAttributesOutputDTO;
use App\Repository\UserRepository;

class ProfileService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}
        
    public function getProfileData($user): ProfileAttributesOutputDTO
        {
            
            $dto = new ProfileAttributesOutputDTO(
                username: $user->getDisplayName(),
                avatarPath: $user->getAvatarPath(),
                locale: $user->getLocale(),
                timezone: $user->getTimezone(),
                isActive: $user->isActive(),
            );
            
            return $dto;
        }

    public function editProfile($user, $input): ProfileAttributesOutputDTO
    {
        if ($input->username !== null && $input->username !== $user->getDisplayName()) {
            $user->setUsername($input->username);
        }
        if ($input->timezone !== null && $input->timezone !== $user->getTimezone()) {
            $user->setTimezone($input->timezone);
        }
        if ($input->locale !== null && $input->locale !== $user->getLocale()) {
            $user->setLocale($input->locale);
        }
        if ($input->avatarPath !== null && $input->avatarPath !== $user->getAvatarPath()) {
            $user->setAvatarPath($input->avatarPath);
        }

        try {
            $this->userRepository->save($user, true);
        }
        catch (\Exception $e) {
            throw new \RuntimeException('Failed to update profile: ' . $e->getMessage());
        }
        return $this->getProfileData($user);
    }

    public function changeEmail($user, $newEmail): void
    {
        // Implementation for changing email
    }

    public function changePassword($user, $newPassword): void
    {
        // Implementation for changing password
    }

    public function resendConfirmationEmail($user): void
    {
        // Implementation for resending confirmation email
    }   
}
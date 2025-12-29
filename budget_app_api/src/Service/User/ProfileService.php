<?php

namespace App\Service\User;

use App\DTO\Common\Output\MessageResponseOutputDTO;
use App\DTO\Profile\Input\UserProfileChangePasswordInputDTO;
use App\DTO\Profile\Input\UserProfileEditInputDTO;
use App\DTO\Profile\Output\EmailChangedOutputDTO;
use App\DTO\Profile\Output\ProfileAttributesOutputDTO;
use App\Entity\EmailChangeRequest;
use App\Entity\User;
use App\Event\ChangeUserEmailEvent;
use App\Event\PasswordChangedEvent;
use App\Repository\EmailChangeRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Service to manage user profiles operations - self profile management
 */
class ProfileService
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailChangeRequestRepository $emailChangeRequestRepository,
        private EventDispatcherInterface $dispatcher,
        private UserService $userService,
        private EntityManagerInterface $entityManager,
    ) {}
    
    /**
     * Get user profile data for his profile
     */
    public function getProfileData(User $user): ProfileAttributesOutputDTO
    {
        $dto = new ProfileAttributesOutputDTO(
            username: $user->getDisplayName(),
            email: $user->getEmail(),
            avatarPath: $user->getAvatarPath(),
            locale: $user->getLocale(),
            timezone: $user->getTimezone(),
            isActive: $user->isActive(),
        );

        return $dto;
    }

    /**
     * Edit user profile for his own profile
     */
    public function editProfile(User $user, UserProfileEditInputDTO $input): ProfileAttributesOutputDTO
    {
        if ($input->username !== null && $input->username !== $user->getDisplayName()) {
            
            $existingUser = $this->userRepository->findOneBy(['username' => $input->username]);

            if ($existingUser !== null && $existingUser->getId() !== $user->getId()) {
                throw new \InvalidArgumentException('This username is already in use.');
            }

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

    /**
     * Initiate user email change process for himself
     */
    public function changeEmail(User $user, string $newEmail, string $password): MessageResponseOutputDTO
    {
        if (!$this->userService->verifyPassword($user, $password)) {
            throw new \InvalidArgumentException('Invalid password provided.');
        }

        $existingUser = $this->userService->findOneByEmail($newEmail);

        if ($existingUser !== null && $existingUser->getId() !== $user->getId()) {
            throw new \InvalidArgumentException('This email is already in use.');
        }

        $token = bin2hex(random_bytes(32));

        $emailChangeRequest = new EmailChangeRequest();
        $emailChangeRequest
            ->setUser($user)
            ->setOldEmail($user->getEmail())
            ->setNewEmail($newEmail)
            ->setToken($token)
            ->setExpiresAt(new \DateTimeImmutable('+24 hours'));

        try{
        $this->emailChangeRequestRepository->save($emailChangeRequest, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to initiate email change: ' . $e->getMessage());
        }

        $event = new ChangeUserEmailEvent($user, $emailChangeRequest);
        $this->dispatcher->dispatch($event);

        return new MessageResponseOutputDTO(
            message: 'A confirmation email has been sent to your new email address.'
        );
    }

    /**
     * Change user password for himself
     */
    public function changePassword(User $user, UserProfileChangePasswordInputDTO $input): MessageResponseOutputDTO
    {
        if (!$this->userService->verifyPassword($user, $input->currentPassword)) {
            throw new \InvalidArgumentException('Cannot proceed.');
        }

        $newHashedPassword = $this->userService->hashPassword($user, $input->newPassword);

        $user->setPassword($newHashedPassword);
        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to change password: ' . $e->getMessage());
        }

        $event = new PasswordChangedEvent($user);
        $this->dispatcher->dispatch($event);

        return new MessageResponseOutputDTO(
            message: 'Your password has been changed successfully. A confirmation email has been sent.'
        );
    }

    /**
     * Confirm email change with token
     */
    public function confirmEmailChange(string $token): EmailChangedOutputDTO
    {
        $emailChangeRequest = $this->emailChangeRequestRepository->findOneBy(['token' => $token]);
        if (!$emailChangeRequest || $emailChangeRequest->getExpiresAt() <= new \DateTimeImmutable()) {
            throw new \InvalidArgumentException('Invalid or expired timming.');
        }
        if($emailChangeRequest->getConfirmedAt() ) {
            $user = $emailChangeRequest->getUser();
            return new EmailChangedOutputDTO(
                message: 'Your new email has been confirmed.',
                email: $user->getEmail()
            );
        }

        $user = $emailChangeRequest->getUser();
        $oldEmail = $emailChangeRequest->getOldEmail();
        $newEmail = $emailChangeRequest->getNewEmail();
        $user->setEmail($newEmail);
        $user->setEmailVerifiedAt(new \DateTimeImmutable());

        ## Remove the email change request to track confirmed requests
        // $user->removeEmailChangeRequest($emailChangeRequest);
        try {
        $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to confirm email change: ' . $e->getMessage());
        }

        $emailChangeRequest->setConfirmedAt(new \DateTimeImmutable());
        $this->emailChangeRequestRepository->save($emailChangeRequest, true);

        // Invalidate existing refresh tokens
        $this->invalidateUserRefreshTokens($oldEmail);

        $event = new \App\Event\EmailChangedEvent($user, $oldEmail, $newEmail);
        $this->dispatcher->dispatch($event);

        return new EmailChangedOutputDTO(
            message: 'Your new email has been confirmed. Please log in again with your new email address.',
            email: $newEmail,
            shouldLogout: true
        );
    } 
    
    /**
     * Invalidate all refresh tokens for a user
     */
    private function invalidateUserRefreshTokens(string $username): void
    {
        // Delete all refresh tokens for this user (username = email)
        $connection = $this->entityManager->getConnection();

        try {
            $connection->executeStatement(
                'DELETE FROM refresh_tokens WHERE username = :username',
                ['username' => $username]
            );
        } catch (\Exception $e) {
            // Log but don't fail the email change if token deletion fails
            error_log('Failed to invalidate refresh tokens: ' . $e->getMessage());
        }
    }

    public function deleteAccount(User $user, string $password, ?string $reason = null): MessageResponseOutputDTO
    {
        if (!$this->userService->verifyPassword($user, $password)) {
            throw new \InvalidArgumentException('Invalid password provided.');
        }

        $user->setDeletedAt(new \DateTimeImmutable());
        if ($reason !== null) {
            $user->setDeletionReason($reason);
        }

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to delete account: ' . $e->getMessage());
        }

        return new MessageResponseOutputDTO(
            message: 'Your account has been successfully deleted.'
        );
    }

}
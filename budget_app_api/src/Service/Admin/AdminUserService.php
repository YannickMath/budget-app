<?php

namespace App\Service\Admin;

use App\DTO\Admin\Input\AdminCreateUserInputDTO;
use App\DTO\Admin\Input\AdminUpdateUserInputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * AdminUserService handles all administrative operations on users
 * Different from UserService (reusable utilities) and ProfileService (self-management)
 */
class AdminUserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
    ) {}

    /**
     * Admin creates a new user
     * Different from public registration - can set roles, verified status, etc.
     */
    public function createUser(AdminCreateUserInputDTO $input): User
    {
        if (!$this->userService->isEmailAvailable($input->email)) {
            throw new UnprocessableEntityHttpException('Cannot proceed with this email.');
        }

        $user = new User();
        $user->setEmail($input->email);
        $user->setUsername($input->username);

        $hashedPassword = $this->userService->hashPassword($user, $input->password);
        $user->setPassword($hashedPassword);

        $user->setRoles($input->roles);
        $user->setTimezone($input->timezone);
        $user->setLocale($input->locale);
        $user->setIsActive($input->isActive);

        if ($input->avatarPath) {
            $user->setAvatarPath($input->avatarPath);
        }

        // Admin can create pre-verified users
        if ($input->emailVerified) {
            $user->setEmailVerifiedAt(new \DateTimeImmutable());
        }

        try {
            $this->userRepository->save($user, true);
        } catch (UniqueConstraintViolationException $e) {
            throw new UnprocessableEntityHttpException('This email or username is already in use.');
        } catch (\Exception $e) {
            throw new \RuntimeException('Error creating user: ' . $e->getMessage());
        }

        return $user;
    }

    /**
     * Admin updates an existing user
     */
    public function updateUser(int $userId, AdminUpdateUserInputDTO $input, User $admin): User
    {
        $user = $this->userService->findOneById($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        // Prevent admin from updating themselves via admin endpoint
        if ($admin->getId() === $user->getId()) {
            throw new UnprocessableEntityHttpException('You cannot update your own account via admin endpoint.');
        }

        $changedFields = [];

        if ($input->email !== null && $input->email !== $user->getEmail()) {
            if (!$this->userService->isEmailAvailable($input->email, $user->getId())) {
                throw new UnprocessableEntityHttpException('This email is already in use.');
            }
            $user->setEmail($input->email);
            $changedFields[] = 'email';
        }

        if ($input->username !== null && $input->username !== $user->getDisplayName()) {
            $user->setUsername($input->username);
            $changedFields[] = 'username';
        }

        if ($input->password !== null) {
            $hashedPassword = $this->userService->hashPassword($user, $input->password);
            $user->setPassword($hashedPassword);
            $changedFields[] = 'password';
        }

        if ($input->timezone !== null && $input->timezone !== $user->getTimezone()) {
            $user->setTimezone($input->timezone);
            $changedFields[] = 'timezone';
        }

        if ($input->locale !== null && $input->locale !== $user->getLocale()) {
            $user->setLocale($input->locale);
            $changedFields[] = 'locale';
        }

        if ($input->roles !== null && $input->roles !== $user->getRoles()) {
            $user->setRoles($input->roles);
            $changedFields[] = 'roles';
        }

        if ($input->isActive !== null && $input->isActive !== $user->isActive()) {
            $user->setIsActive($input->isActive);
            $changedFields[] = 'isActive';
        }

        if ($input->avatarPath !== null && $input->avatarPath !== $user->getAvatarPath()) {
            $user->setAvatarPath($input->avatarPath);
            $changedFields[] = 'avatarPath';
        }

        // Admin can manually verify/unverify emails
        if ($input->emailVerified !== null) {
            $newVerifiedAt = $input->emailVerified ? new \DateTimeImmutable() : null;
            if ($newVerifiedAt != $user->getEmailVerifiedAt()) {
                $user->setEmailVerifiedAt($newVerifiedAt);
                $changedFields[] = 'emailVerified';
            }
        }

        if (empty($changedFields)) {
            throw new UnprocessableEntityHttpException('No changes were made to the user.');
        }

        try {
            $this->userRepository->save($user, true);
        } catch (UniqueConstraintViolationException $e) {
            throw new UnprocessableEntityHttpException('This email or username is already in use.');
        } catch (\Exception $e) {
            throw new \RuntimeException('Error updating user: ' . $e->getMessage());
        }

        return $user;
    }

    /**
     * Admin soft-deletes a user
     */
    public function deleteUser(int $userId, User $admin, ?string $reason = null): void
    {
        $user = $this->userService->findOneById($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        // Prevent admin from deleting themselves
        if ($user->getId() === $admin->getId()) {
            throw new UnprocessableEntityHttpException('You cannot delete your own account.');
        }

        $user->setDeletedAt(new \DateTimeImmutable());
        $user->setIsActive(false);
        $user->setDeletionReason($reason);

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Admin restores a soft-deleted user
     */
    public function restoreUser(int $userId, User $admin): User
    {
        $user = $this->userService->findOneById($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        if (!$user->getDeletedAt()) {
            throw new UnprocessableEntityHttpException('User is not deleted.');
        }

        // Prevent admin from restoring themselves (they shouldn't be deleted in first place)
        if ($user->getId() === $admin->getId()) {
            throw new UnprocessableEntityHttpException('You cannot restore your own account.');
        }

        $user->setDeletedAt(null);
        $user->setIsActive(true);

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error restoring user: ' . $e->getMessage());
        }

        return $user;
    }

    /**
     * Admin changes user roles
     */
    public function assignRoles(int $userId, array $roles, User $admin): User
    {
        $user = $this->userService->findOneById($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        // Prevent admin from modifying their own roles
        if ($user->getId() === $admin->getId()) {
            throw new UnprocessableEntityHttpException('You cannot modify your own roles.');
        }

        $user->setRoles($roles);

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error updating user roles: ' . $e->getMessage());
        }

        return $user;
    }

    /**
     * Admin force-verifies a user's email
     */
    public function forceVerifyEmail(int $userId, User $admin): User
    {
        $user = $this->userService->findOneById($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        if ($user->getEmailVerifiedAt()) {
            throw new UnprocessableEntityHttpException('Email is already verified.');
        }

        // Note: Admin cannot verify their own email if needed (no restriction here)
        if ($user->getId() === $admin->getId()) {
            throw new UnprocessableEntityHttpException('You cannot verify your own email via admin endpoint.');
        }

        $user->setEmailVerifiedAt(new \DateTimeImmutable());

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error verifying email: ' . $e->getMessage());
        }

        return $user;
    }

    /**
     * Admin resets a user's password
     */
    public function resetUserPassword(int $userId, string $newPassword, User $admin): User
    {
        $user = $this->userService->findOneById($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        // Prevent admin from resetting their own password via admin endpoint
        if ($user->getId() === $admin->getId()) {
            throw new UnprocessableEntityHttpException('You cannot reset your own password. Use the profile endpoint instead.');
        }

        $hashedPassword = $this->userService->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error resetting password: ' . $e->getMessage());
        }

        return $user;
    }

    /**
     * Admin activates a user account
     */
    public function activateUser(int $userId, User $admin): User
    {
        $user = $this->userService->findOneById($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        if ($user->isActive()) {
            throw new UnprocessableEntityHttpException('User is already active.');
        }

        // Note: Admin cannot activate their own account if needed (edge case, but allowed)
        if ($user->getId() === $admin->getId()) {
            throw new UnprocessableEntityHttpException('You cannot activate your own account.');
        }

        $user->setIsActive(true);

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error activating user: ' . $e->getMessage());
        }

        return $user;
    }

    /**
     * Admin deactivates a user account
     */
    public function desactivateUser(int $userId, User $admin): User
    {
        $user = $this->userService->findOneById($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        // Prevent admin from deactivating themselves
        if ($user->getId() === $admin->getId()) {
            throw new UnprocessableEntityHttpException('You cannot deactivate your own account.');
        }

        if (!$user->isActive()) {
            throw new UnprocessableEntityHttpException('User is already inactive.');
        }

        $user->setIsActive(false);

        try {
            $this->userRepository->save($user, true);

        } catch (\Exception $e) {
            throw new \RuntimeException('Error deactivating user: ' . $e->getMessage());
        }

        return $user;
    }

}
<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Admin\AdminUserService;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly AdminUserService $adminUserService,
    ) {}

    #[Route('/api/admin/users/{id}/desactivate', name: 'app_admin_deactivate_user', methods: ['POST'])]
    public function desactivateUser(int $id): JsonResponse
    {
        try {
            $this->adminUserService->desactivateUser($id, $this->getUser());
            return $this->json(['message' => 'User has been deactivated successfully.'], 200);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Failed to deactivate user: ' . $e->getMessage()], 400);
        }
    }

    #[Route('/api/admin/users/{id}/activate', name: 'app_admin_activate_user', methods: ['POST'])]
    public function activateUser(int $id): JsonResponse
    {
        try {
            $this->adminUserService->activateUser($id, $this->getUser());
            return $this->json(['message' => 'User has been activated successfully.'], 200);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Failed to activate user: ' . $e->getMessage()], 400);
        }
    }

    #[Route('/api/admin/users/{id}/restore', name: 'app_admin_restore_user', methods: ['POST'])]
    public function restoreUser(int $id): JsonResponse
    {
        try {
            $this->adminUserService->restoreUser($id, $this->getUser());
            return $this->json(['message' => 'User has been restored successfully.'], 200);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Failed to restore user: ' . $e->getMessage()], 400);
        }
    }

    #[Route('/api/admin/users/{id}/roles', name: 'app_admin_assign_roles', methods: ['POST'])]
    public function assignRoles(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $roles = $data['roles'] ?? null;

        if (!$roles || !is_array($roles)) {
            return $this->json(['message' => 'Roles array is required.'], 400);
        }

        try {
            $this->adminUserService->assignRoles($id, $roles, $this->getUser());
            return $this->json(['message' => 'Roles have been assigned successfully.'], 200);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Failed to assign roles: ' . $e->getMessage()], 400);
        }
    }

    #[Route('/api/admin/users/{id}/verify-email', name: 'app_admin_verify_email', methods: ['POST'])]
    public function forceVerifyEmail(int $id): JsonResponse
    {
        try {
            $this->adminUserService->forceVerifyEmail($id, $this->getUser());
            return $this->json(['message' => 'Email has been verified successfully.'], 200);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Failed to verify email: ' . $e->getMessage()], 400);
        }
    }

    #[Route('/api/admin/users/{id}/reset-password', name: 'app_admin_reset_password', methods: ['POST'])]
    public function resetUserPassword(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $newPassword = $data['password'] ?? null;

        if (!$newPassword) {
            return $this->json(['message' => 'New password is required.'], 400);
        }

        try {
            $this->adminUserService->resetUserPassword($id, $newPassword, $this->getUser());
            return $this->json(['message' => 'Password has been reset successfully.'], 200);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Failed to reset password: ' . $e->getMessage()], 400);
        }
    }

}
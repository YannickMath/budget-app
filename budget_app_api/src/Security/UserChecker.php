<?php

## documentation: https://symfony.com/doc/current/security/user_checkers.html ##
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getDeletedAt() !== null) {
            throw new CustomUserMessageAccountStatusException(
                'Your account has been deleted. Please contact support for more information.'
            );
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Your account has been deactivated. Please contact support.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

    }
}
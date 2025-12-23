<?php

## documentation: https://symfony.com/doc/current/security/user_checkers.html ##
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getEmailVerifiedAt() === null) {
            throw new CustomUserMessageAccountStatusException(
                'Votre adresse email n\'a pas été vérifiée. Veuillez consulter votre boîte mail pour activer votre compte.'
            );
        }

        if ($user->getDeletedAt() !== null) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été supprimé. Veuillez contacter le support pour plus d\'informations.'
            );
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été désactivé. Veuillez contacter le support.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Vérifications supplémentaires après authentification si nécessaire
    }
}
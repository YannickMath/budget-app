<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Event subscriber to handle actions on successful login
 */
final class AuthLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private LoggerInterface $logger,
    )
    {
    }
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event) : void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        // Log la connexion réussie
        $this->logger->info('User logged in successfully', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'last_login' => $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
        ]);

        $user->setLastLoginAt(new \DateTimeImmutable());
        $this->userRepository->save($user, true);

        $this->logger->debug('User last login updated', [
            'user_id' => $user->getId(),
        ]);
    }
}
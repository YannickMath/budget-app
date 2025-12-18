<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserRepository $userRepository,
    )
    {
    }
    public static function getSubscribedEvents()
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
        $user->setLastLoginAt(new \DateTimeImmutable());
        $this->userRepository->save($user, true);
    }
}
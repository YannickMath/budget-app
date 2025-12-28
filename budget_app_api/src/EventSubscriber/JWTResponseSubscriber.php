<?php

namespace App\EventSubscriber;

use App\DTO\Auth\Output\UserOutputDTO;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to enrich JWT authentication response with user data
 *
 * This subscriber listens to the JWT authentication success event and adds
 * user information to the response, ensuring consistency between login and
 * registration endpoints.
 */
class JWTResponseSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $data['user'] = UserOutputDTO::fromEntity($user);

        $event->setData($data);
    }
}
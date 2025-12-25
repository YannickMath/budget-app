<?php

namespace App\EventSubscriber\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\Event\DeauthenticatedEvent;
class PasswordChangedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            // LogoutEvent::class => 'onLogout',
            // InteractiveLoginEvent::class => 'onInteractiveLogin',
            // SwitchUserEvent::class => 'onSwitchUser',
            // DeauthenticatedEvent::class => 'onDeauthenticated',
        ];
    }
}
<?php

namespace App\EventSubscriber;

use App\Config\EmailConfig;
use App\Event\PasswordChangedEvent;
use App\Message\SendEmailMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Sends a confirmation email when a user changes their password
 */
final class PasswordChangedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PasswordChangedEvent::class => 'onPasswordChanged',
        ];
    }

    public function onPasswordChanged(PasswordChangedEvent $event): void
    {
        $user = $event->getUser();

        $message = new SendEmailMessage(
            to: $user->getEmail(),
            subject: EmailConfig::SUBJECT_PASSWORD_CHANGED,
            template: 'emails/password_changed.html.twig',
            locale: $user->getLocale(),
            context: [
                'username' => $user->getDisplayName(),
                'changedAt' => $event->getChangedAt(),
            ]
        );

        $this->messageBus->dispatch($message);
    }
}

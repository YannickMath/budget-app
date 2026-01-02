<?php

namespace App\EventSubscriber;

use App\Config\EmailConfig;
use App\Event\EmailChangedEvent;
use App\Message\SendEmailMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Sends a notification email to the old email address when a user's email is changed
 */
final class EmailChangedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            EmailChangedEvent::class => 'onEmailChanged',
        ];
    }

    public function onEmailChanged(EmailChangedEvent $event): void
    {
        $user = $event->getUser();
        $oldEmail = $event->getOldEmail();
        $newEmail = $event->getNewEmail();

        // Send notification to OLD email address (security measure)
        $message = new SendEmailMessage(
            to: $oldEmail,
            subject: EmailConfig::SUBJECT_EMAIL_CHANGED,
            template: 'emails/change_email_notification.html.twig',
            locale: $user->getLocale(),
            context: [
                'username' => $user->getDisplayName(),
                'oldEmail' => $oldEmail,
                'newEmail' => $newEmail,
                'changedAt' => $event->getChangedAt(),
            ]
        );

        $this->messageBus->dispatch($message);
    }
}

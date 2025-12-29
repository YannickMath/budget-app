<?php

namespace App\EventSubscriber;

use App\Config\EmailConfig;
use App\Event\EmailChangedEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Sends a notification email to the old email address when a user's email is changed
 */
final class EmailChangedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerInterface $mailer
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
        $email = (new TemplatedEmail())
            ->from(EmailConfig::NOREPLY_EMAIL)
            ->to($oldEmail)
            ->subject(EmailConfig::SUBJECT_EMAIL_CHANGED)
            ->htmlTemplate('emails/change_email_notification.html.twig')
            ->locale($user->getLocale())
            ->context([
                'username' => $user->getDisplayName(),
                'oldEmail' => $oldEmail,
                'newEmail' => $newEmail,
                'changedAt' => $event->getChangedAt(),
            ]);

        $this->mailer->send($email);
    }
}

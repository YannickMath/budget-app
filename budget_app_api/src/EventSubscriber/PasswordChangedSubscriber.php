<?php

namespace App\EventSubscriber;

use App\Event\PasswordChangedEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Sends a confirmation email when a user changes their password
 */
class PasswordChangedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerInterface $mailer
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

        $email = (new TemplatedEmail())
            ->from('security@budgetapp.com')
            ->to($user->getEmail())
            ->subject('Votre mot de passe a été modifié - Budget App')
            ->htmlTemplate('emails/password_changed.html.twig')
            ->locale($user->getLocale())
            ->context([
                'username' => $user->getDisplayName(),
                'changed_at' => $event->getChangedAt(),
            ]);

        $this->mailer->send($email);
    }
}

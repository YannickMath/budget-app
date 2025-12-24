<?php

namespace App\EventSubscriber\Security;

use App\Event\ForgotPasswordEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class ForgotPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
    )
    {
    }
    public static function getSubscribedEvents(): array
    {
        return [
        ForgotPasswordEvent::class => 'onForgotPassword',
        ];
    }

    public function onForgotPassword(ForgotPasswordEvent $event): void
    {
       $user = $event->getUser();
        // Construction de l'URL vers le frontend
        // TODO: Remplacer par une variable d'environnement FRONTEND_URL en production
        $resetUrl = sprintf(
            'http://localhost:3000/reset-password?token=%s',
            $user->getPasswordResetToken()
        );

        $email = (new TemplatedEmail())
            ->from('noreply@budget-app.com')
            ->to($user->getEmail())
            ->subject('Réinitialisez votre mot de passe - Budget App')
            ->htmlTemplate('emails/forgot_password.html.twig')
            ->locale($user->getLocale())
            ->context([
                'reset_url' => $resetUrl,
                'username' => $user->getDisplayName(),
                'expiration_date' => $user->getPasswordResetTokenExpiresAt(),
            ]);

        $this->mailer->send($email);

    }
    }

   
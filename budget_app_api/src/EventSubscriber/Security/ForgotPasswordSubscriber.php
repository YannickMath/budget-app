<?php

namespace App\EventSubscriber\Security;

use App\Event\ForgotPasswordEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ForgotPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire(env: 'FRONTEND_URL')]
        private string $frontendUrl
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
        $resetUrl = sprintf(
            '%s/reset-password?token=%s',
            $this->frontendUrl,
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

   
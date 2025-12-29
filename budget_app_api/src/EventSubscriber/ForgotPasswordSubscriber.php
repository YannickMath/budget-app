<?php

namespace App\EventSubscriber;

use App\Config\EmailConfig;
use App\Event\ForgotPasswordEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Event subscriber to handle forgot password email sending
 */
final class ForgotPasswordSubscriber implements EventSubscriberInterface
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
        
        $resetUrl = sprintf(
            '%s/reset-password?token=%s',
            $this->frontendUrl,
            $user->getPasswordResetToken()
        );

        $email = (new TemplatedEmail())
            ->from(EmailConfig::NOREPLY_EMAIL)
            ->to($user->getEmail())
            ->subject(EmailConfig::SUBJECT_PASSWORD_RESET)
            ->htmlTemplate('emails/forgot_password.html.twig')
            ->locale($user->getLocale())
            ->context([
                'resetUrl' => $resetUrl,
                'username' => $user->getDisplayName(),
                'expirationDate' => $user->getPasswordResetTokenExpiresAt(),
            ]);

        $this->mailer->send($email);

        }
    }
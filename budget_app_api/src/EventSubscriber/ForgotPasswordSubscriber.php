<?php

namespace App\EventSubscriber;

use App\Config\EmailConfig;
use App\Event\ForgotPasswordEvent;
use App\Message\SendEmailMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Event subscriber to handle forgot password email sending
 */
final class ForgotPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
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

        $message = new SendEmailMessage(
            to: $user->getEmail(),
            subject: EmailConfig::SUBJECT_PASSWORD_RESET,
            template: 'emails/forgot_password.html.twig',
            locale: $user->getLocale(),
            context: [
                'resetUrl' => $resetUrl,
                'username' => $user->getDisplayName(),
                'expirationDate' => $user->getPasswordResetTokenExpiresAt(),
            ]
        );

        $this->messageBus->dispatch($message);
        }
    }
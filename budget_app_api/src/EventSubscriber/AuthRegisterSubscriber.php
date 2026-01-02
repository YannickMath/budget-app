<?php

namespace App\EventSubscriber;

use App\Config\EmailConfig;
use App\Event\RegisterSuccessEvent;
use App\Message\SendEmailMessage;
use App\Service\Auth\AuthService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Event subscriber to handle user registration and send verification emails
 */
final class AuthRegisterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AuthService $authService,
        private MessageBusInterface $messageBus,
        #[Autowire(env: 'API_URL')]
        private string $apiUrl
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            RegisterSuccessEvent::class => "onRegisterSuccess",
        ];
    }

    public function onRegisterSuccess(RegisterSuccessEvent $event): void
    {
        $user = $event->getUser();

        $this->authService->generateEmailVerificationToken($user);

        $verificationUrl = sprintf(
            '%s/api/auth/verify-email?token=%s',
            $this->apiUrl,
            $user->getEmailVerificationToken()
        );

        $message = new SendEmailMessage(
            to: $user->getEmail(),
            subject: EmailConfig::SUBJECT_VERIFY_EMAIL,
            template: 'emails/signup.html.twig',
            locale: $user->getLocale(),
            context: [
                'username' => $user->getDisplayName(),
                'verificationUrl' => $verificationUrl,
                'expirationDate' => $user->getEmailVerificationTokenExpiresAt(),
            ]
        );

        $this->messageBus->dispatch($message);
    }
}
<?php

namespace App\EventSubscriber;

use App\Config\EmailConfig;
use App\Event\RegisterSuccessEvent;
use App\Service\Auth\AuthService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Event subscriber to handle user registration and send verification emails
 */
final class AuthRegisterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AuthService $authService,
        private MailerInterface $mailer,
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

        $email = (new TemplatedEmail())
            ->from(EmailConfig::NOREPLY_EMAIL)
            ->to($user->getEmail())
            ->subject(EmailConfig::SUBJECT_VERIFY_EMAIL)
            ->htmlTemplate('emails/signup.html.twig')
            ->locale($user->getLocale())
            ->context([
                'username' => $user->getDisplayName(),
                'verificationUrl' => $verificationUrl,
                'expirationDate' => $user->getEmailVerificationTokenExpiresAt(),
            ]);

        $this->mailer->send($email);
    }
}
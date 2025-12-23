<?php

namespace App\EventSubscriber\Auth;

use App\Event\RegisterSuccessEvent;
use App\Service\Auth\EmailVerificationService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class AuthRegisterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailVerificationService $emailVerificationService,
        private MailerInterface $mailer
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

        $this->emailVerificationService->generateVerificationToken($user);

        // Construction de l'URL de vérification
        // TODO: Utiliser FRONTEND_URL quand le frontend sera prêt
        $verificationUrl = sprintf(
            'http://localhost:8000/api/auth/verify-email?token=%s',
            $user->getEmailVerificationToken()
        );

        $email = (new TemplatedEmail())
            ->from('noreply@budget-app.com')
            ->to($user->getEmail())
            ->subject('Vérifiez votre adresse email - Budget App')
            ->htmlTemplate('emails/signup.html.twig')
            ->locale($user->getLocale())
            ->context([
                'username' => $user->getDisplayName(),
                'verification_url' => $verificationUrl,
                'expiration_date' => $user->getEmailVerificationTokenExpiresAt(),
            ]);

        $this->mailer->send($email);
    }
}
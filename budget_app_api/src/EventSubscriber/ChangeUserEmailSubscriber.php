<?php

namespace App\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Event\ChangeUserEmailEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Event subscriber to handle user email change confirmation emails
 */
class ChangeUserEmailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire(env: 'FRONTEND_URL')]
        private string $frontendUrl
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ChangeUserEmailEvent::class => 'onChangeUserEmail',
        ];
    }

    public function onChangeUserEmail(ChangeUserEmailEvent $event): void
    {
        $user = $event->getUser();

        $changeEmailRequest = $event->getEmailChangeRequest();
       
        $token = $changeEmailRequest->getToken();

        $emailChangeUrl = sprintf(
            '%s/profile/me/confirmation-new-email?token=%s',
            $this->frontendUrl,
            $token
        );

        $email = (new TemplatedEmail())
            ->from('no-reply@budgetapp.com')
            ->to($changeEmailRequest->getNewEmail())
            ->subject('Confirmez votre changement d\'adresse e-mail - Budget App')
            ->htmlTemplate('user/profile/change_email.html.twig')
            ->locale($user->getLocale())
            ->context([
                'email_change_url' => $emailChangeUrl,
                'username' => $user->getDisplayName(),
                'expiration_date' => $changeEmailRequest->getExpiresAt(),
            ]);

        $this->mailer->send($email);
    }

}
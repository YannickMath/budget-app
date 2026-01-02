<?php

namespace App\EventSubscriber;

use App\Config\EmailConfig;
use App\Event\ChangeUserEmailEvent;
use App\Message\SendEmailMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Event subscriber to handle user email change confirmation emails
 */
final class ChangeUserEmailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
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

        $message = new SendEmailMessage(
            to: $changeEmailRequest->getNewEmail(),
            subject: EmailConfig::SUBJECT_EMAIL_CHANGE_CONFIRMATION,
            template: 'emails/change_email_request.html.twig',
            locale: $user->getLocale(),
            context: [
                'emailChangeUrl' => $emailChangeUrl,
                'username' => $user->getDisplayName(),
                'expirationDate' => $changeEmailRequest->getExpiresAt(),
            ]
        );

        $this->messageBus->dispatch($message);
    }

}
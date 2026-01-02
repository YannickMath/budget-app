<?php

namespace App\MessageHandler;

use App\Config\EmailConfig;
use App\Message\SendEmailMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler for async email sending
 */
#[AsMessageHandler]
final readonly class SendEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function __invoke(SendEmailMessage $message): void
    {
        $email = (new TemplatedEmail())
            ->from(EmailConfig::NOREPLY_EMAIL)
            ->to($message->getTo())
            ->subject($message->getSubject())
            ->htmlTemplate($message->getTemplate())
            ->locale($message->getLocale())
            ->context($message->getContext());

        $this->mailer->send($email);
    }
}

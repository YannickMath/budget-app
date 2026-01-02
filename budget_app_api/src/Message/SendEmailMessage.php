<?php

namespace App\Message;

/**
 * Message to send an email asynchronously via the message queue
 */
final readonly class SendEmailMessage
{
    public function __construct(
        private string $to,
        private string $subject,
        private string $template,
        private string $locale,
        private array $context = []
    ) {}

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}

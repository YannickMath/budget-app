<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a user's email has been successfully changed
 */
class EmailChangedEvent extends Event
{
    public function __construct(
        private readonly User $user,
        private readonly string $oldEmail,
        private readonly string $newEmail,
        private readonly \DateTimeImmutable $changedAt = new \DateTimeImmutable()
    ) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getOldEmail(): string
    {
        return $this->oldEmail;
    }

    public function getNewEmail(): string
    {
        return $this->newEmail;
    }

    public function getChangedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }
}

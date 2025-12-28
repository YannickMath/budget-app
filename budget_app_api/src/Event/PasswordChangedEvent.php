<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a user changes their password
 */
class PasswordChangedEvent extends Event
{
    public function __construct(
        private readonly User $user,
        private readonly \DateTimeImmutable $changedAt = new \DateTimeImmutable()
    ) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChangedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }
}

<?php

namespace App\Event;

use App\Entity\EmailChangeRequest;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a user requests an email change
 */
class ChangeUserEmailEvent extends Event
{
    public function __construct(
        private User $user,
        private EmailChangeRequest $emailChangeRequest

    ) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEmailChangeRequest(): EmailChangeRequest
    {
        return $this->emailChangeRequest;
    }
}
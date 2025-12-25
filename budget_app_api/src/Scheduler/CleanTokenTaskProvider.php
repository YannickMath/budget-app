<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Message\Auth\CleanupExpiredTokensMessage;

#[AsSchedule('clean_expired_tokens')]
class CleanTokenTaskProvider implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {}
    
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->stateful($this->cache) // ensure missed tasks are executed
            ->processOnlyLastMissedRun(true) // ensure only last missed task is run
            // ->add(
            //     RecurringMessage::cron('0 0 * * *', new CleanExpiredTokensMessage())
            // );
            ## every day at midnight
            ->add(RecurringMessage::cron('@daily', new CleanupExpiredTokensMessage()));
    }
}
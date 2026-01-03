<?php

namespace App\Scheduler;

use App\Message\CleanupOldAuditsMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('clean_old_audits')]
final class CleanAuditTaskProvider implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {}

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)

            // Premier jour du mois à minuit (garde 6 mois d'historique)
            ->add(RecurringMessage::cron('0 0 1 * *', new CleanupOldAuditsMessage(180)));
    }
}

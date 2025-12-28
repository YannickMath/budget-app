<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * Event subscriber to limit login attempts using rate limiting
 */
class LoginRateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RateLimiterFactoryInterface $loginLimiter,
        private readonly RequestStack $requestStack,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', 9999],
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || $request->getPathInfo() !== '/api/login_check') {
            return;
        }

        $limiterKey = $request->getClientIp() ?? 'unknown';

        $limit = $this->loginLimiter->create($limiterKey);
        ## consume method allows to check and consume tokens
        if (!$limit->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(
                retryAfter: $limit->consume(1)->getRetryAfter()->getTimestamp() - time(),
                message: 'Trop de tentatives de connexion. Veuillez réessayer plus tard.'
            );
        }
    }
}
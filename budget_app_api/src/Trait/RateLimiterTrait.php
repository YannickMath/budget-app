<?php

namespace App\Trait;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

trait RateLimiterTrait
{
    /**
     * Apply rate limiting based on the user's IP address
     *
     * @param RateLimiterFactoryInterface $limiter The rate limiter factory to use
     * @param Request $request The current request
     * @throws TooManyRequestsHttpException When rate limit is exceeded
     */
    private function applyRateLimit(RateLimiterFactoryInterface $limiter, Request $request): void
    {
        $limiterKey = $request->getClientIp() ?? 'unknown';

        $limit = $limiter->create($limiterKey);

        if (!$limit->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(
                retryAfter: $limit->consume(1)->getRetryAfter()->getTimestamp() - time(),
                message: 'Trop de tentatives. Veuillez réessayer plus tard.'
            );
        }
    }
}
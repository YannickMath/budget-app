<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Subscriber that enforces email verification requirement for specific API endpoints.
 *
 * This subscriber automatically checks if the authenticated user has verified their email
 * before allowing access to protected endpoints. It runs on every HTTP request after
 * authentication.
 *
 * Behavior:
 * - Allows all GET/OPTIONS requests (read-only operations)
 * - Allows whitelisted routes (auth endpoints, resend verification)
 * - Blocks all write operations (POST/PUT/PATCH/DELETE) for unverified users
 */
class EmailVerificationRequiredSubscriber implements EventSubscriberInterface
{
    /**
     * Routes that do NOT require email verification
     * These patterns are checked with preg_match()
     */
    private const WHITELIST_PATTERNS = [
        '#^/api/auth/#',                        // All auth endpoints (register, verify-email, forgot-password, resend-verification-email, etc.)
        '#^/api/login_check$#',                 // JWT login endpoint
    ];

    /**
     * HTTP methods that are always allowed (read-only operations)
     */
    private const ALLOWED_METHODS = ['GET', 'OPTIONS', 'HEAD'];

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            // Priority 5: After firewall authentication (priority 8) but before controller
            KernelEvents::REQUEST => ['onKernelRequest', 5],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {   
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();
        $method = $request->getMethod();

        foreach (self::WHITELIST_PATTERNS as $pattern) {
            if (preg_match($pattern, $path)) {
                return; // Route is whitelisted, allow access
            }
        }

        if (in_array($method, self::ALLOWED_METHODS, true)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        if ($user->getEmailVerifiedAt() === null) {
            throw new AccessDeniedHttpException(
                'EMAIL_NOT_VERIFIED: Please verify your email address to access this feature.'
            );
        }
    }
}
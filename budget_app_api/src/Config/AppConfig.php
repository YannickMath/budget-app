<?php

namespace App\Config;

/**
 * Application-wide configuration constants
 */
final class AppConfig
{
    /**
     * Default timezone for new users
     */
    public const DEFAULT_TIMEZONE = 'Europe/Paris';

    /**
     * Default locale for new users
     */
    public const DEFAULT_LOCALE = 'fr';

    /**
     * Available locales
     */
    public const AVAILABLE_LOCALES = ['fr', 'en'];

    /**
     * Prevent instantiation
     */
    private function __construct()
    {
    }
}

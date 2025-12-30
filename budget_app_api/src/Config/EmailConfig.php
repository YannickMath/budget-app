<?php

namespace App\Config;

/**
 * Email configuration constants
 */
final class EmailConfig
{
    /**
     * No-reply email address for automated emails
     */
    public const NOREPLY_EMAIL = 'noreply@budget-app.com';

    /**
     * Support email address for customer support
     */
    public const SUPPORT_EMAIL = 'support@budget-app.com';

    /**
     * Email subjects
     */
    public const SUBJECT_VERIFY_EMAIL = 'Verify your email address - Budget App';
    public const SUBJECT_PASSWORD_RESET = 'Reset your password - Budget App';
    public const SUBJECT_PASSWORD_CHANGED = 'Your password has been changed - Budget App';
    public const SUBJECT_EMAIL_CHANGE_CONFIRMATION = 'Confirm your email address change - Budget App';
    public const SUBJECT_EMAIL_CHANGED = 'Your email address has been changed - Budget App';

    /**
     * Prevent instantiation
     */
    private function __construct()
    {
    }
}

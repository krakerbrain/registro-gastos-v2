<?php
class EmailConfig
{
    public static string $SMTP_HOST;
    public static string $SMTP_USER;
    public static string $SMTP_PASS;
    public static string $SMTP_PORT;
    public static string $FROM_EMAIL;
    public static string $FROM_NAME;

    public static function init(): void
    {
        self::$SMTP_HOST = $_ENV['SMTP_HOST'] ?? '';
        self::$SMTP_USER = $_ENV['SMTP_USER'] ?? '';
        self::$SMTP_PASS = $_ENV['SMTP_PASS'] ?? '';
        self::$FROM_EMAIL = $_ENV['FROM_EMAIL'] ?? '';
        self::$FROM_NAME = $_ENV['FROM_NAME'] ?? '';
    }
}

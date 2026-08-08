<?php
final class Config
{
    private const VALUES = [
        'APP_NAME' => 'AutoValue',
        'BASE_URL' => '/ai-driven-market-analysis-and-vehicle-valuation-app/web',
        'DB_HOST' => '127.0.0.1',
        'DB_NAME' => 'autovalue',
        'DB_USER' => 'root',
        'DB_PASS' => '',
        'AI_ENDPOINT' => 'http://127.0.0.1:5000/predict',
        'AI_TIMEOUT' => 25,
        'STRIPE_SECRET_KEY' => '',
        'STRIPE_PUBLISHABLE_KEY' => '',
        'CURRENCY' => 'LKR',
        'MAX_IMAGES_PER_AD' => 10,
        'MAX_IMAGE_BYTES' => 5242880,
        'MAX_DOC_BYTES' => 10485760,
        'MAX_FAILED_LOGINS' => 5,
        'LOGIN_LOCK_MINUTES' => 15,
        'ADS_PER_PAGE' => 9,
        'PROMO_DURATION_DAYS' => 7,
    ];

    public static function get(string $key, $default = null)
    {
        return self::VALUES[$key] ?? $default;
    }

    public static function baseUrl(string $path = ''): string
    {
        return rtrim(self::VALUES['BASE_URL'], '/') . '/' . ltrim($path, '/');
    }

    public static function rootPath(string $path = ''): string
    {
        return rtrim(dirname(__DIR__), '/') . '/' . ltrim($path, '/');
    }
}

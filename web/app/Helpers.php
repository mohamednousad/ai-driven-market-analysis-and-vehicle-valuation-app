<?php
final class Helpers
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . Config::baseUrl($path));
        exit;
    }

    public static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    public static function price($amount): string
    {
        return Config::get('CURRENCY') . ' ' . number_format((float)$amount, 0);
    }

    public static function img(?string $path, string $fallback): string
    {
        if (!$path) {
            return $fallback;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        if (file_exists(Config::rootPath($path))) {
            return Config::baseUrl($path);
        }
        return $fallback;
    }

    public static function avatar(?string $path): string
    {
        return self::img($path, 'https://ui-avatars.com/api/?background=D4AF37&color=141400&name=AV');
    }

    public static function timeAgo(?string $datetime): string
    {
        if (!$datetime) {
            return '';
        }
        $diff = time() - strtotime($datetime);
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        return date('d M Y', strtotime($datetime));
    }

    public static function post(string $key, $default = ''): string
    {
        return trim((string)($_POST[$key] ?? $default));
    }

    public static function get(string $key, $default = ''): string
    {
        return trim((string)($_GET[$key] ?? $default));
    }
}

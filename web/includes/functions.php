<?php
require_once __DIR__ . '/../config/config.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function base_url(string $path = ''): string
{
    return $path;
}

function post(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function query(string $key, $default = '')
{
    return $_GET[$key] ?? $default;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function format_money($amount, string $symbol = 'Rs'): string
{
    return $symbol . ' ' . number_format((float)$amount, 0);
}

function time_ago(string $datetime): string
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) {
        return 'just now';
    }
    $units = [
        31536000 => 'year',
        2592000 => 'month',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
    ];
    foreach ($units as $seconds => $label) {
        if ($diff >= $seconds) {
            $count = floor($diff / $seconds);
            return $count . ' ' . $label . ($count > 1 ? 's' : '') . ' ago';
        }
    }
    return 'just now';
}

function status_badge_class(string $status): string
{
    switch ($status) {
        case AD_STATUS_APPROVED:
            return 'badge-approved';
        case AD_STATUS_REJECTED:
            return 'badge-rejected';
        default:
            return 'badge-pending';
    }
}

function star_rating_html(float $rating): string
{
    $rating = max(0, min(5, $rating));
    $full = (int)floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $html = '<span class="stars" title="' . number_format($rating, 1) . ' out of 5">';
    $html .= str_repeat('<i class="fa-solid fa-star"></i>', $full);
    if ($half) {
        $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
    }
    $html .= str_repeat('<i class="fa-regular fa-star"></i>', $empty);
    $html .= '</span>';
    return $html;
}

function old(string $key, $default = '')
{
    return e((string)($_SESSION['old'][$key] ?? $default));
}

function set_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

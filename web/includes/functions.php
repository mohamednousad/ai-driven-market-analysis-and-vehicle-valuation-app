<?php
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money(float $amount): string
{
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 0);
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function csrfVerify(): void
{
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? null)) {
        http_response_code(419);
        exit('Invalid session token. Go back and try again.');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 2592000) return floor($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($datetime));
}

function posterBadge(string $posterType): string
{
    if ($posterType === 'member') {
        return '<span class="badge badge-member"><i class="fa-solid fa-circle-check"></i> MEMBER</span>';
    }
    if ($posterType === 'authorized_agent') {
        return '<span class="badge badge-agent"><i class="fa-solid fa-shield-halved"></i> AUTHORIZED AGENT</span>';
    }
    return '';
}

function statusChip(string $status): string
{
    $map = [
        'pending_review' => ['chip-pending', 'Pending review'],
        'approved'       => ['chip-approved', 'Live'],
        'rejected'       => ['chip-rejected', 'Rejected'],
        'sold'           => ['chip-sold', 'Sold'],
    ];
    [$cls, $label] = $map[$status] ?? ['chip-pending', $status];
    return '<span class="chip ' . $cls . '">' . e($label) . '</span>';
}

function promoTag(?string $type): string
{
    if ($type === 'featured') return '<span class="promo-tag promo-featured">FEATURED</span>';
    if ($type === 'urgent') return '<span class="promo-tag promo-urgent">URGENT</span>';
    if ($type === 'top_ad') return '<span class="promo-tag promo-top">TOP AD</span>';
    return '';
}

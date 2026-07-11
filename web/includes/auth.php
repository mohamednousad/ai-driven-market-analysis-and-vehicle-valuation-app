<?php
require_once __DIR__ . '/functions.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}

function current_role(): string
{
    return (string)($_SESSION['role'] ?? '');
}

function current_name(): string
{
    return (string)($_SESSION['full_name'] ?? '');
}

function has_role(string $role): bool
{
    return current_role() === $role;
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please login to continue.');
        redirect('login.php');
    }
}

function require_role(string $role): void
{
    require_login();
    if (current_role() !== $role) {
        set_flash('error', 'You do not have access to that area.');
        redirect(role_dashboard(current_role()));
    }
}

function redirect_if_logged_in(): void
{
    if (is_logged_in()) {
        redirect(role_dashboard(current_role()));
    }
}

function role_dashboard(string $role): string
{
    switch ($role) {
        case ROLE_ADMIN:
            return 'admin/dashboard.php';
        case ROLE_SELLER:
            return 'seller/dashboard.php';
        case ROLE_BUYER:
            return 'buyer/dashboard.php';
        default:
            return 'login.php';
    }
}

function login_user(array $user): void
{
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
}

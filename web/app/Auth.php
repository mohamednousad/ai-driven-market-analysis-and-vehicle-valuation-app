<?php
final class Auth
{
    public static function attempt(string $email, string $password, string $ip): array
    {
        $db = Database::getInstance();
        $user = $db->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
        if (!$user) {
            return ['ok' => false, 'error' => 'No account found for that email address.'];
        }
        if ($user['status'] === 'suspended') {
            return ['ok' => false, 'error' => 'This account has been suspended. Contact support.'];
        }
        if (self::isLocked((int)$user['id'])) {
            return ['ok' => false, 'error' => 'Too many failed attempts. Try again in ' . Config::get('LOGIN_LOCK_MINUTES') . ' minutes.'];
        }
        if (!password_verify($password, $user['password'])) {
            self::logAttempt((int)$user['id'], $ip, 'failed');
            return ['ok' => false, 'error' => 'Incorrect password. Please try again.'];
        }
        self::logAttempt((int)$user['id'], $ip, 'success');
        Session::regenerate();
        Session::set('user_id', (int)$user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['username']);
        return ['ok' => true, 'user' => $user];
    }

    private static function isLocked(int $userId): bool
    {
        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT COUNT(*) AS fails FROM login_logs WHERE user_id = ? AND status = 'failed' AND attempted_at > (NOW() - INTERVAL ? MINUTE)",
            [$userId, (int)Config::get('LOGIN_LOCK_MINUTES')]
        );
        return (int)($row['fails'] ?? 0) >= (int)Config::get('MAX_FAILED_LOGINS');
    }

    private static function logAttempt(int $userId, string $ip, string $status): void
    {
        Database::getInstance()->insert(
            'INSERT INTO login_logs (user_id, ip_address, status, attempted_at) VALUES (?, ?, ?, NOW())',
            [$userId, $ip, $status]
        );
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function check(): bool
    {
        return Session::get('user_id') !== null;
    }

    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id === null ? null : (int)$id;
    }

    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return Database::getInstance()->fetchOne('SELECT * FROM users WHERE id = ?', [self::id()]);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::warning('Please log in to continue.');
            Helpers::redirect('login.php');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            Flash::error('You do not have permission to access that page.');
            Helpers::redirect('index.php');
        }
    }

    public static function requireCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!Session::verifyCsrf($token)) {
            if (Helpers::isAjax()) {
                Helpers::json(['ok' => false, 'error' => 'Security token expired. Refresh the page and try again.'], 419);
            }
            Flash::error('Security token expired. Please try again.');
            Helpers::redirect('index.php');
        }
    }
}

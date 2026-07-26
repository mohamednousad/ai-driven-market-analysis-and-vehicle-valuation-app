<?php
class Auth
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user']) ? (int)$_SESSION['user']['user_id'] : null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Please log in first.');
            redirect('/login.php');
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if (self::role() !== $role) {
            http_response_code(403);
            exit('You do not have permission to view this page.');
        }
    }

    public function attempt(string $email, string $password): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->logAttempt(null, $email, false, 'unknown_email');
            return [false, 'Invalid email or password.'];
        }

        $recentFails = $this->recentFailures((int)$user['user_id']);
        if ($recentFails >= MAX_FAILED_LOGINS) {
            $this->logAttempt((int)$user['user_id'], $email, false, 'locked');
            return [false, 'Account temporarily locked after too many failed attempts. Try again in ' . LOCK_MINUTES . ' minutes.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            $this->logAttempt((int)$user['user_id'], $email, false, 'bad_password');
            return [false, 'Invalid email or password.'];
        }

        if ($user['status'] === 'banned' || $user['status'] === 'suspended') {
            $this->logAttempt((int)$user['user_id'], $email, false, 'account_' . $user['status']);
            return [false, 'This account is ' . $user['status'] . '. Contact support.'];
        }

        $this->logAttempt((int)$user['user_id'], $email, true, null);

        $_SESSION['user'] = [
            'user_id'     => (int)$user['user_id'],
            'full_name'   => $user['full_name'],
            'email'       => $user['email'],
            'role'        => $user['role'],
            'poster_type' => $user['poster_type'],
        ];
        session_regenerate_id(true);
        return [true, null];
    }

    private function recentFailures(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM user_login_attempts
             WHERE user_id = ? AND was_successful = 0
               AND attempted_at > DATE_SUB(NOW(), INTERVAL ' . (int)LOCK_MINUTES . ' MINUTE)'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    private function logAttempt(?int $userId, string $email, bool $success, ?string $reason): void
    {
        $this->pdo->prepare(
            'INSERT INTO user_login_attempts (user_id, email_attempted, was_successful, failure_reason)
             VALUES (?, ?, ?, ?)'
        )->execute([$userId, $email, $success ? 1 : 0, $reason]);
    }

    public function register(string $name, string $email, string $password, string $role, string $phone): array
    {
        if (!in_array($role, ['buyer', 'seller'], true)) {
            return [false, 'Invalid account type.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Please enter a valid email address.'];
        }
        if (strlen($password) < 8) {
            return [false, 'Password must be at least 8 characters.'];
        }

        $stmt = $this->pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return [false, 'An account with this email already exists.'];
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                "INSERT INTO users (full_name, email, password_hash, role, status)
                 VALUES (?, ?, ?, ?, 'active')"
            )->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            $userId = (int)$this->pdo->lastInsertId();
            $this->pdo->prepare('INSERT INTO user_registration (user_id, phone) VALUES (?, ?)')
                ->execute([$userId, $phone]);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            return [false, 'Registration failed. Please try again.'];
        }
        return [true, null];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}

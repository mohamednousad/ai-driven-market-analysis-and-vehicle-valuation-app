<?php
class AuthService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function register(string $fullName, string $email, string $password, string $role, string $phone = ''): array
    {
        if (!$fullName || !$email || !$password) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must contain at least 6 characters.'];
        }
        if (!in_array($role, [ROLE_BUYER, ROLE_SELLER], true)) {
            return ['success' => false, 'message' => 'Please choose a valid account type.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash, role, phone) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$fullName, $email, $hash, $role, $phone]);
            return ['success' => true, 'message' => 'Account created successfully. You can now login.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'This email is already registered.'];
        }
    }

    public function login(string $email, string $password): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        if ((int)$user['is_active'] === 0) {
            return ['success' => false, 'message' => 'Your account has been suspended. Contact support.'];
        }
        login_user($user);
        return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
    }
}

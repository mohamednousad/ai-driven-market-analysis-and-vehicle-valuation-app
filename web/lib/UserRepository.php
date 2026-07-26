<?php
class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        return $this->pdo->query(
            'SELECT u.user_id, u.full_name, u.email, u.role, u.poster_type, u.status, u.created_at, r.phone
             FROM users u LEFT JOIN user_registration r ON r.user_id = u.user_id
             ORDER BY u.created_at DESC LIMIT 300'
        )->fetchAll();
    }

    public function setStatus(int $userId, string $status): void
    {
        if (!in_array($status, ['active', 'suspended', 'banned'], true)) {
            return;
        }
        $this->pdo->prepare('UPDATE users SET status = ? WHERE user_id = ? AND role <> 'admin'')
            ->execute([$status, $userId]);
    }

    public function setPosterType(int $userId, string $type): void
    {
        if (!in_array($type, ['non_member', 'member', 'authorized_agent'], true)) {
            return;
        }
        $this->pdo->prepare('UPDATE users SET poster_type = ? WHERE user_id = ?')->execute([$type, $userId]);
    }

    public function counts(): array
    {
        $rows = $this->pdo->query('SELECT role, COUNT(*) AS c FROM users GROUP BY role')->fetchAll();
        $out = ['admin' => 0, 'seller' => 0, 'buyer' => 0];
        foreach ($rows as $r) {
            $out[$r['role']] = (int)$r['c'];
        }
        return $out;
    }
}

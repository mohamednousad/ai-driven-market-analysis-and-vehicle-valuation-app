<?php
class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(string $role = ''): array
    {
        $sql = 'SELECT id, full_name, email, role, phone, is_active, created_at FROM users';
        $params = [];
        if ($role) {
            $sql .= ' WHERE role = ?';
            $params[] = $role;
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function setActive(int $id, int $active): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET is_active = ? WHERE id = ? AND role <> ?');
        $stmt->execute([$active, $id, ROLE_ADMIN]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ? AND role <> ?');
        $stmt->execute([$id, ROLE_ADMIN]);
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total FROM users WHERE role = ?');
        $stmt->execute([$role]);
        return (int)$stmt->fetch()['total'];
    }

    public function updateProfile(int $id, string $fullName, string $phone): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?');
        $stmt->execute([$fullName, $phone, $id]);
    }
}

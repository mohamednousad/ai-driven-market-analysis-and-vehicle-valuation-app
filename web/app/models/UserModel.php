<?php
class UserModel extends BaseModel
{
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function create(string $username, string $email, string $password): int
    {
        return $this->db->insert(
            'INSERT INTO users (username, email, password) VALUES (?, ?, ?)',
            [$username, $email, password_hash($password, PASSWORD_DEFAULT)]
        );
    }

    public function updateProfileImage(int $id, string $path): void
    {
        $this->db->execute('UPDATE users SET profile_image = ? WHERE id = ?', [$path, $id]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->db->execute('UPDATE users SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT u.*, sp.seller_type FROM users u LEFT JOIN seller_profile sp ON sp.user_id = u.id ORDER BY u.created_at DESC'
        );
    }

    public function countAll(): int
    {
        return (int)($this->db->fetchOne('SELECT COUNT(*) AS c FROM users')['c'] ?? 0);
    }
}

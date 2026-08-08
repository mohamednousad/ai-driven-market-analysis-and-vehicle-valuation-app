<?php
class AuthorizedRequestModel extends BaseModel
{
    public function pendingForUser(int $userId): ?array
    {
        return $this->db->fetchOne("SELECT * FROM authorized_seller_requests WHERE user_id = ? AND status = 'pending'", [$userId]);
    }

    public function latestForUser(int $userId): ?array
    {
        return $this->db->fetchOne('SELECT * FROM authorized_seller_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1', [$userId]);
    }

    public function create(int $userId, string $name, string $description): int
    {
        return $this->db->insert(
            'INSERT INTO authorized_seller_requests (user_id, business_name, business_description) VALUES (?, ?, ?)',
            [$userId, $name, $description]
        );
    }

    public function attachDocument(int $id, string $path): void
    {
        $this->db->execute('UPDATE authorized_seller_requests SET business_document = ? WHERE id = ?', [$path, $id]);
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT r.*, u.username, u.email FROM authorized_seller_requests r JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC'
        );
    }

    public function review(int $id, int $adminId, string $status, string $reason = ''): ?array
    {
        $this->db->execute(
            'UPDATE authorized_seller_requests SET status = ?, rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?',
            [$status, $reason ?: null, $adminId, $id]
        );
        return $this->db->fetchOne('SELECT * FROM authorized_seller_requests WHERE id = ?', [$id]);
    }

    public function pendingCount(): int
    {
        return (int)($this->db->fetchOne("SELECT COUNT(*) AS c FROM authorized_seller_requests WHERE status = 'pending'")['c'] ?? 0);
    }
}

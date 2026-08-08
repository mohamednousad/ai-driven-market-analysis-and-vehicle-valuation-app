<?php
class PaymentModel extends BaseModel
{
    public function create(int $userId, string $category, float $amount, string $method, ?int $subscriptionId = null): int
    {
        return $this->db->insert(
            "INSERT INTO payments (user_id, subscription_id, category, amount, method, status) VALUES (?, ?, ?, ?, ?, 'pending')",
            [$userId, $subscriptionId, $category, $amount, $method]
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM payments WHERE id = ?', [$id]);
    }

    public function complete(int $id, string $transactionId, ?int $subscriptionId = null): void
    {
        $this->db->execute(
            "UPDATE payments SET status = 'completed', transaction_id = ?, subscription_id = COALESCE(?, subscription_id), paid_at = NOW() WHERE id = ?",
            [$transactionId, $subscriptionId, $id]
        );
    }

    public function fail(int $id): void
    {
        $this->db->execute("UPDATE payments SET status = 'failed' WHERE id = ?", [$id]);
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT p.*, u.username FROM payments p JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC');
    }

    public function forUser(int $userId): array
    {
        return $this->db->fetchAll('SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC', [$userId]);
    }

    public function revenueThisMonth(): float
    {
        return (float)($this->db->fetchOne("SELECT COALESCE(SUM(amount),0) AS s FROM payments WHERE status = 'completed' AND MONTH(paid_at) = MONTH(NOW()) AND YEAR(paid_at) = YEAR(NOW())")['s'] ?? 0);
    }
}

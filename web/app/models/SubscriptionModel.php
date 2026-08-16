<?php
class SubscriptionModel extends BaseModel
{
    public function plans(): array
    {
        return $this->db->fetchAll("SELECT * FROM subscription_plans WHERE status = 'active' ORDER BY price ASC");
    }

    public function planById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM subscription_plans WHERE id = ?', [$id]);
    }

    public function activeForUser(int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT s.*, p.name AS plan_name, p.price AS plan_price, p.free_promotions, p.grants_member_badge
             FROM subscriptions s JOIN subscription_plans p ON p.id = s.plan_id
             WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE()
             ORDER BY s.end_date DESC LIMIT 1",
            [$userId]
        );
    }

    public function activate(int $userId, array $plan): int
    {
        $this->db->execute("UPDATE subscriptions SET status = 'cancelled' WHERE user_id = ? AND status = 'active'", [$userId]);
        $subId = $this->db->insert(
            "INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY), 'active')",
            [$userId, (int)$plan['id'], (int)$plan['duration']]
        );
        $this->db->insert(
            'INSERT INTO promotion_usage (subscription_id, total_limit, used_count, remaining_count) VALUES (?, ?, 0, ?)',
            [$subId, (int)$plan['free_promotions'], (int)$plan['free_promotions']]
        );
        if ((int)$plan['grants_member_badge'] === 1) {
            $this->db->execute("UPDATE seller_profile SET seller_type = 'member' WHERE user_id = ? AND seller_type = 'non_member'", [$userId]);
        }
        return $subId;
    }

    public function promotionUsage(int $subscriptionId): ?array
    {
        return $this->db->fetchOne('SELECT * FROM promotion_usage WHERE subscription_id = ?', [$subscriptionId]);
    }

    public function consumeFreePromotion(int $subscriptionId): bool
    {
        $affected = $this->db->execute(
            'UPDATE promotion_usage SET used_count = used_count + 1, remaining_count = remaining_count - 1 WHERE subscription_id = ? AND remaining_count > 0',
            [$subscriptionId]
        );
        return $affected > 0;
    }
}

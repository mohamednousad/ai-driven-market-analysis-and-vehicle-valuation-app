<?php
class PromotionModel extends BaseModel
{
    public function create(int $adId, ?int $paymentId, string $type, float $amount, int $days): int
    {
        return $this->db->insert(
            'INSERT INTO ad_promotions (ad_id, payment_id, type, amount, starts_at, ends_at) VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))',
            [$adId, $paymentId, $type, $amount, $days]
        );
    }

    public function activeForAd(int $adId): ?array
    {
        return $this->db->fetchOne('SELECT * FROM ad_promotions WHERE ad_id = ? AND NOW() BETWEEN starts_at AND ends_at ORDER BY id DESC LIMIT 1', [$adId]);
    }
}

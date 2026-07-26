<?php
class PromotionRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function purchase(int $adId, string $type, int $days): bool
    {
        $prices = unserialize(PROMO_PRICES);
        if (!isset($prices[$type]) || $days < 1 || $days > 30) {
            return false;
        }
        $this->pdo->prepare(
            "INSERT INTO ad_promotions (ad_id, promotion_type, amount, payment_status, starts_at, ends_at)
             VALUES (?, ?, ?, 'paid', NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))"
        )->execute([$adId, $type, $prices[$type] * $days, $days]);
        return true;
    }

    public function activeForAd(int $adId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM ad_promotions
             WHERE ad_id = ? AND payment_status = 'paid'
               AND (starts_at IS NULL OR starts_at <= NOW())
               AND (ends_at IS NULL OR ends_at >= NOW())
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$adId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

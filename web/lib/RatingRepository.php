<?php
class RatingRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function rate(int $adId, int $sellerId, int $buyerId, int $rating, string $comment): bool
    {
        if ($rating < 1 || $rating > 5) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            'INSERT INTO buyer_seller_ratings (ad_id, seller_id, buyer_id, rating, comment)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)'
        );
        $stmt->execute([$adId, $sellerId, $buyerId, $rating, $comment]);
        return true;
    }

    public function sellerSummary(int $sellerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average
             FROM buyer_seller_ratings WHERE seller_id = ?'
        );
        $stmt->execute([$sellerId]);
        $row = $stmt->fetch();
        return ['total' => (int)$row['total'], 'average' => round((float)$row['average'], 1)];
    }

    public function forAdByBuyer(int $adId, int $buyerId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM buyer_seller_ratings WHERE ad_id = ? AND buyer_id = ? LIMIT 1'
        );
        $stmt->execute([$adId, $buyerId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function recentForSeller(int $sellerId, int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.rating, r.comment, r.created_at, u.full_name AS buyer_name
             FROM buyer_seller_ratings r JOIN users u ON u.user_id = r.buyer_id
             WHERE r.seller_id = ? ORDER BY r.created_at DESC LIMIT ' . (int)$limit
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }
}

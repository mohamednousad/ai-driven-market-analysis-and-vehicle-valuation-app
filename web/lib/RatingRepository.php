<?php
class RatingRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function rate(int $sellerId, int $buyerId, int $adId, int $rating, string $comment): array
    {
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 1 and 5 stars.'];
        }
        if ($sellerId === $buyerId) {
            return ['success' => false, 'message' => 'You cannot rate your own account.'];
        }
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO seller_ratings (seller_id, buyer_id, ad_id, rating, comment)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)'
            );
            $stmt->execute([$sellerId, $buyerId, $adId, $rating, $comment]);
            return ['success' => true, 'message' => 'Thank you, your rating has been saved.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Unable to save your rating right now.'];
        }
    }

    public function summary(int $sellerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT AVG(rating) AS avg_rating, COUNT(*) AS rating_count
             FROM seller_ratings WHERE seller_id = ?'
        );
        $stmt->execute([$sellerId]);
        $row = $stmt->fetch();
        return [
            'avg_rating' => (float)($row['avg_rating'] ?? 0),
            'rating_count' => (int)($row['rating_count'] ?? 0),
        ];
    }

    public function forSeller(int $sellerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT sr.*, u.full_name AS buyer_name
             FROM seller_ratings sr
             JOIN users u ON u.id = sr.buyer_id
             WHERE sr.seller_id = ?
             ORDER BY sr.created_at DESC'
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }
}

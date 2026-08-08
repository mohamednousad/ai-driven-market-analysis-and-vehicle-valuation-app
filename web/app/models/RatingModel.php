<?php
class RatingModel extends BaseModel
{
    public function add(int $sellerId, int $buyerId, int $adId, int $rating, string $comment): bool
    {
        try {
            $this->db->insert(
                'INSERT INTO seller_ratings (seller_id, buyer_id, ad_id, rating, comment) VALUES (?, ?, ?, ?, ?)',
                [$sellerId, $buyerId, $adId, $rating, $comment]
            );
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function forSeller(int $sellerId): array
    {
        return $this->db->fetchAll(
            'SELECT r.*, u.username AS buyer_name, a.title AS ad_title FROM seller_ratings r JOIN users u ON u.id = r.buyer_id JOIN ads a ON a.id = r.ad_id WHERE r.seller_id = ? ORDER BY r.id DESC',
            [$sellerId]
        );
    }
}

<?php
class FeedbackModel extends BaseModel
{
    public function add(int $buyerId, int $adId, string $comment): void
    {
        $this->db->insert('INSERT INTO ad_feedback (buyer_id, ad_id, comment) VALUES (?, ?, ?)', [$buyerId, $adId, $comment]);
    }

    public function forAd(int $adId): array
    {
        return $this->db->fetchAll(
            'SELECT f.*, u.username, u.profile_image FROM ad_feedback f JOIN users u ON u.id = f.buyer_id WHERE f.ad_id = ? ORDER BY f.id DESC',
            [$adId]
        );
    }
}

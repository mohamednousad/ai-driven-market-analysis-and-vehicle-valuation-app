<?php
class FavouriteRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function toggle(int $buyerId, int $adId): bool
    {
        $stmt = $this->pdo->prepare('SELECT favourite_id FROM buyer_favourites WHERE buyer_id = ? AND ad_id = ?');
        $stmt->execute([$buyerId, $adId]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            $this->pdo->prepare('DELETE FROM buyer_favourites WHERE favourite_id = ?')->execute([$existing]);
            return false;
        }
        $this->pdo->prepare('INSERT INTO buyer_favourites (buyer_id, ad_id) VALUES (?, ?)')->execute([$buyerId, $adId]);
        return true;
    }

    public function isFavourite(int $buyerId, int $adId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM buyer_favourites WHERE buyer_id = ? AND ad_id = ?');
        $stmt->execute([$buyerId, $adId]);
        return (bool)$stmt->fetchColumn();
    }

    public function idsForBuyer(int $buyerId): array
    {
        $stmt = $this->pdo->prepare('SELECT ad_id FROM buyer_favourites WHERE buyer_id = ?');
        $stmt->execute([$buyerId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}

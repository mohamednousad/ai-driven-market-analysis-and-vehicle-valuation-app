<?php
class FavouriteModel extends BaseModel
{
    public function toggle(int $buyerId, int $adId): bool
    {
        $exists = $this->db->fetchOne('SELECT id FROM buyer_favourites WHERE buyer_id = ? AND ad_id = ?', [$buyerId, $adId]);
        if ($exists) {
            $this->db->execute('DELETE FROM buyer_favourites WHERE id = ?', [(int)$exists['id']]);
            return false;
        }
        $this->db->insert('INSERT INTO buyer_favourites (buyer_id, ad_id) VALUES (?, ?)', [$buyerId, $adId]);
        return true;
    }

    public function isFavourite(int $buyerId, int $adId): bool
    {
        return (bool)$this->db->fetchOne('SELECT id FROM buyer_favourites WHERE buyer_id = ? AND ad_id = ?', [$buyerId, $adId]);
    }

    public function adIdsForUser(int $buyerId): array
    {
        return array_column($this->db->fetchAll('SELECT ad_id FROM buyer_favourites WHERE buyer_id = ?', [$buyerId]), 'ad_id');
    }
}

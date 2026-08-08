<?php
class ChatModel extends BaseModel
{
    public function findOrCreate(int $adId, int $buyerId, int $sellerId): int
    {
        $existing = $this->db->fetchOne('SELECT id FROM chats WHERE ad_id = ? AND buyer_id = ?', [$adId, $buyerId]);
        if ($existing) {
            return (int)$existing['id'];
        }
        return $this->db->insert('INSERT INTO chats (ad_id, buyer_id, seller_id) VALUES (?, ?, ?)', [$adId, $buyerId, $sellerId]);
    }

    public function listForUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, a.title AS ad_title, a.price AS ad_price,
             (SELECT image_path FROM vehicle_images vi JOIN vehicle_info v ON v.id = vi.vehicle_id WHERE v.ad_id = a.id ORDER BY vi.is_primary DESC LIMIT 1) AS ad_image,
             IF(c.buyer_id = ?, s.username, b.username) AS other_name,
             IF(c.buyer_id = ?, s.profile_image, b.profile_image) AS other_avatar,
             (SELECT message FROM messages m WHERE m.chat_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
             (SELECT created_at FROM messages m WHERE m.chat_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_time,
             (SELECT COUNT(*) FROM messages m WHERE m.chat_id = c.id AND m.receiver_id = ? AND m.is_read = 0) AS unread
             FROM chats c
             JOIN ads a ON a.id = c.ad_id
             JOIN users b ON b.id = c.buyer_id
             JOIN users s ON s.id = c.seller_id
             WHERE c.buyer_id = ? OR c.seller_id = ?
             ORDER BY COALESCE(last_time, c.created_at) DESC",
            [$userId, $userId, $userId, $userId, $userId]
        );
    }

    public function find(int $chatId, int $userId): ?array
    {
        return $this->db->fetchOne(
            'SELECT c.*, a.title AS ad_title, a.price AS ad_price, a.seller_id AS ad_seller,
             (SELECT image_path FROM vehicle_images vi JOIN vehicle_info v ON v.id = vi.vehicle_id WHERE v.ad_id = a.id ORDER BY vi.is_primary DESC LIMIT 1) AS ad_image
             FROM chats c JOIN ads a ON a.id = c.ad_id
             WHERE c.id = ? AND (c.buyer_id = ? OR c.seller_id = ?)',
            [$chatId, $userId, $userId]
        );
    }

    public function messages(int $chatId): array
    {
        return $this->db->fetchAll('SELECT * FROM messages WHERE chat_id = ? ORDER BY id ASC', [$chatId]);
    }

    public function send(int $chatId, int $senderId, int $receiverId, string $message): int
    {
        return $this->db->insert(
            'INSERT INTO messages (chat_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)',
            [$chatId, $senderId, $receiverId, $message]
        );
    }

    public function markRead(int $chatId, int $userId): void
    {
        $this->db->execute('UPDATE messages SET is_read = 1 WHERE chat_id = ? AND receiver_id = ?', [$chatId, $userId]);
    }

    public function unreadCount(int $userId): int
    {
        return (int)($this->db->fetchOne('SELECT COUNT(*) AS c FROM messages WHERE receiver_id = ? AND is_read = 0', [$userId])['c'] ?? 0);
    }
}

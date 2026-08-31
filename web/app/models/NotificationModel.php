<?php
class NotificationModel extends BaseModel
{
    public function push(int $userId, string $title, string $message, string $type, ?int $adId = null): void
    {
        $this->db->insert(
            'INSERT INTO notifications (user_id, ad_id, title, message, type) VALUES (?, ?, ?, ?, ?)',
            [$userId, $adId, $title, $message, $type]
        );
    }

    public function forUser(int $userId, int $limit = 30): array
    {
        return $this->db->fetchAll('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT ?', [$userId, $limit]);
    }

    public function unreadCount(int $userId): int
    {
        return (int)($this->db->fetchOne('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0', [$userId])['c'] ?? 0);
    }

    public function markAllRead(int $userId): void
    {
        $this->db->execute('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [$userId]);
    }
}

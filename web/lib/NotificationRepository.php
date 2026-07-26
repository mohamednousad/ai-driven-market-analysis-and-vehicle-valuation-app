<?php
class NotificationRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function push(int $userId, ?int $adId, string $title, string $message, string $type): void
    {
        $this->pdo->prepare(
            'INSERT INTO notifications (user_id, ad_id, title, message, type) VALUES (?, ?, ?, ?, ?)'
        )->execute([$userId, $adId, $title, $message, $type]);
    }

    public function forUser(int $userId, int $limit = 30): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int)$limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markAllRead(int $userId): void
    {
        $this->pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$userId]);
    }
}

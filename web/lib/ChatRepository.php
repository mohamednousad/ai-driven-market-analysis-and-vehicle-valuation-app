<?php
class ChatRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function openOrCreate(int $adId, int $buyerId): int
    {
        $stmt = $this->pdo->prepare('SELECT chat_id FROM buyer_chats WHERE ad_id = ? AND buyer_id = ?');
        $stmt->execute([$adId, $buyerId]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int)$id;
        }
        $this->pdo->prepare('INSERT INTO buyer_chats (ad_id, buyer_id) VALUES (?, ?)')->execute([$adId, $buyerId]);
        return (int)$this->pdo->lastInsertId();
    }

    public function find(int $chatId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, a.title AS ad_title, a.seller_id, a.price,
                    b.full_name AS buyer_name, s.full_name AS seller_name
             FROM buyer_chats c
             JOIN ads a ON a.ad_id = c.ad_id
             JOIN users b ON b.user_id = c.buyer_id
             JOIN users s ON s.user_id = a.seller_id
             WHERE c.chat_id = ? LIMIT 1'
        );
        $stmt->execute([$chatId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function messages(int $chatId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.*, u.full_name AS sender_name
             FROM buyer_chat_messages m JOIN users u ON u.user_id = m.sender_id
             WHERE m.chat_id = ? ORDER BY m.created_at ASC'
        );
        $stmt->execute([$chatId]);
        return $stmt->fetchAll();
    }

    public function addMessage(int $chatId, int $senderId, string $text): void
    {
        $this->pdo->prepare(
            'INSERT INTO buyer_chat_messages (chat_id, sender_id, message_text) VALUES (?, ?, ?)'
        )->execute([$chatId, $senderId, $text]);

        $chat = $this->find($chatId);
        if ($chat && $senderId === (int)$chat['seller_id'] && $chat['status'] === 'open') {
            $this->pdo->prepare("UPDATE buyer_chats SET status = 'responded' WHERE chat_id = ?")->execute([$chatId]);
        }
    }

    public function inboxFor(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.*, a.title AS ad_title, a.seller_id,
                    b.full_name AS buyer_name, s.full_name AS seller_name,
                    (SELECT message_text FROM buyer_chat_messages m WHERE m.chat_id = c.chat_id
                      ORDER BY m.created_at DESC LIMIT 1) AS last_message,
                    (SELECT MAX(m.created_at) FROM buyer_chat_messages m WHERE m.chat_id = c.chat_id) AS last_at
             FROM buyer_chats c
             JOIN ads a ON a.ad_id = c.ad_id
             JOIN users b ON b.user_id = c.buyer_id
             JOIN users s ON s.user_id = a.seller_id
             WHERE c.buyer_id = ? OR a.seller_id = ?
             ORDER BY COALESCE(last_at, c.created_at) DESC"
        );
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }

    public function canAccess(array $chat, int $userId): bool
    {
        return $userId === (int)$chat['buyer_id'] || $userId === (int)$chat['seller_id'];
    }
}

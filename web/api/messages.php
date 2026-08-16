<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
$userId = (int)Auth::id();
$chatModel = new ChatModel();
$action = Helpers::post('action');
if ($action === 'start') {
    $adId = (int)Helpers::post('ad_id');
    $ad = (new AdModel())->findFull($adId);
    if (!$ad) {
        Helpers::json(['ok' => false, 'error' => 'That listing no longer exists.'], 404);
    }
    if ((int)$ad['seller_id'] === $userId) {
        Helpers::json(['ok' => false, 'error' => 'You cannot start a chat on your own ad.'], 422);
    }
    $chatId = $chatModel->findOrCreate($adId, $userId, (int)$ad['seller_id']);
    Helpers::json(['ok' => true, 'chat_id' => $chatId]);
}
if ($action === 'send') {
    $chatId = (int)Helpers::post('chat_id');
    $chat = $chatModel->find($chatId, $userId);
    if (!$chat) {
        Helpers::json(['ok' => false, 'error' => 'Chat not found.'], 404);
    }
    if (($chat['status'] ?? '') === 'closed') {
        Helpers::json(['ok' => false, 'error' => 'This chat has been closed.'], 422);
    }
    $message = trim(Helpers::post('message'));
    if ($message === '' || mb_strlen($message) > 2000) {
        Helpers::json(['ok' => false, 'error' => 'Message must be between 1 and 2000 characters.'], 422);
    }
    $receiverId = (int)$chat['buyer_id'] === $userId ? (int)$chat['seller_id'] : (int)$chat['buyer_id'];
    $chatModel->send($chatId, $userId, $receiverId, $message);
    (new NotificationModel())->push($receiverId, 'New Message', 'You have a new message about "' . $chat['ad_title'] . '".', 'message', (int)$chat['ad_id']);
    Helpers::json(['ok' => true]);
}
if ($action === 'fetch') {
    $chatId = (int)Helpers::post('chat_id');
    $chat = $chatModel->find($chatId, $userId);
    if (!$chat) {
        Helpers::json(['ok' => false, 'error' => 'Chat not found.'], 404);
    }
    $chatModel->markRead($chatId, $userId);
    $items = array_map(fn($m) => [
        'message' => $m['message'],
        'mine' => (int)$m['sender_id'] === $userId,
        'time_ago' => Helpers::timeAgo($m['created_at']),
    ], $chatModel->messages($chatId));
    Helpers::json(['ok' => true, 'items' => $items]);
}
Helpers::json(['ok' => false, 'error' => 'Unknown action.'], 400);

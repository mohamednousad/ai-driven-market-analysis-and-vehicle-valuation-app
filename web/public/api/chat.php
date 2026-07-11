<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/SettingsService.php';
require_once __DIR__ . '/../../lib/GeminiChat.php';

require_role(ROLE_BUYER);

$payload = read_json_body();
$message = trim((string)($payload['message'] ?? ''));
$history = is_array($payload['history'] ?? null) ? $payload['history'] : [];

if ($message === '') {
    json_response(['success' => false, 'message' => 'Please type a message.'], 422);
}

$settings = new SettingsService($pdo);
$chat = new GeminiChat(
    (string)$settings->get('gemini_api_key', ''),
    (string)$settings->get('gemini_model', 'gemini-1.5-flash')
);

$result = $chat->ask($message, $history);

if (($result['success'] ?? false) === true) {
    $stmt = $pdo->prepare('INSERT INTO chat_messages (user_id, role, message) VALUES (?, ?, ?)');
    $stmt->execute([current_user_id(), 'user', $message]);
    $stmt->execute([current_user_id(), 'assistant', $result['reply']]);
}

json_response($result);

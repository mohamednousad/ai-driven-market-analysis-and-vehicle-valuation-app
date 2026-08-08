<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
$userId = (int)Auth::id();
$notifModel = new NotificationModel();
$action = Helpers::post('action');
if ($action === 'list') {
    $items = array_map(fn($n) => [
        'title' => $n['title'],
        'message' => $n['message'],
        'type' => $n['type'],
        'is_read' => (int)$n['is_read'],
        'time_ago' => Helpers::timeAgo($n['created_at']),
    ], $notifModel->forUser($userId, 8));
    Helpers::json(['ok' => true, 'items' => $items]);
}
if ($action === 'mark_read') {
    $notifModel->markAllRead($userId);
    Helpers::json(['ok' => true]);
}
Helpers::json(['ok' => false, 'error' => 'Unknown action.'], 400);

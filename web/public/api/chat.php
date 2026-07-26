<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
header('Content-Type: application/json');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim((string)($input['message'] ?? ''));
$history = is_array($input['history'] ?? null) ? $input['history'] : [];

if ($message === '') {
    echo json_encode(['success' => false, 'message' => 'Empty message.']);
    exit;
}

$clean = [];
foreach (array_slice($history, -10) as $turn) {
    if (is_array($turn) && isset($turn['role'], $turn['text'])) {
        $clean[] = ['role' => (string)$turn['role'], 'text' => (string)$turn['text']];
    }
}

echo json_encode((new GeminiChat())->ask($clean, $message));

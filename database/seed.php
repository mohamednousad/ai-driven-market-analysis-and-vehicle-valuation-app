<?php
require __DIR__ . '/../web/config/database.php';

$pdo = Database::getConnection();
$hash = password_hash('password123', PASSWORD_DEFAULT);

$accounts = [
    ['AutoValue Admin', 'admin@autovalue.lk', 'admin', 'non_member', '0112345678'],
    ['Demo Seller', 'seller@autovalue.lk', 'seller', 'member', '0771234567'],
    ['Demo Agent', 'agent@autovalue.lk', 'seller', 'authorized_agent', '0779876543'],
    ['Demo Buyer', 'buyer@autovalue.lk', 'buyer', 'non_member', '0765554443'],
];

foreach ($accounts as [$name, $email, $role, $poster, $phone]) {
    $exists = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
    $exists->execute([$email]);
    if ($exists->fetch()) {
        echo "skip {$email}\n";
        continue;
    }
    $pdo->prepare(
        "INSERT INTO users (full_name, email, password_hash, role, poster_type, status)
         VALUES (?, ?, ?, ?, ?, 'active')"
    )->execute([$name, $email, $hash, $role, $poster]);
    $userId = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO user_registration (user_id, phone) VALUES (?, ?)')
        ->execute([$userId, $phone]);
    echo "created {$email}\n";
}
echo "Done. Password for all: password123\n";

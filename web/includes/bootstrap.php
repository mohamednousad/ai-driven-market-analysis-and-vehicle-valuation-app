<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/functions.php';

spl_autoload_register(function (string $class): void {
    $file = dirname(__DIR__) . '/lib/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$pdo = Database::getConnection();

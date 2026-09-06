<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

spl_autoload_register(function (string $class): void {
    $paths = [
        __DIR__ . '/' . $class . '.php',
        __DIR__ . '/models/' . $class . '.php',
        __DIR__ . '/observers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

set_exception_handler(function (Throwable $e): void {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (Helpers::isAjax()) {
        Helpers::json(['ok' => false, 'error' => 'Something went wrong on the server. Please try again.'], 500);
    }
    http_response_code(500);
    echo '<h1 style="font-family:sans-serif">Something went wrong</h1><p style="font-family:sans-serif">Please go back and try again.</p>';
    exit;
});

Session::start();

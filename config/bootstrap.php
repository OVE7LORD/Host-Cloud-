<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('date.timezone', 'UTC');

// сесия с параметрами защиты
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true
]);


require_once __DIR__ . '/../vendor/autoload.php';


\HostCloud\Core\ErrorHandler::register();

// Database connection will be handled by individual classes as needed
// Initialize security settings
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true
    ]);
}

// Вспомогательные функции CSRF
if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token(string $token): bool {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

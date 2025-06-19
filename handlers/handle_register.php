<?php
declare(strict_types=1);

use HostCloud\Parts\Auth;

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // валидация
    $username = trim($_POST['username'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($username) || $email === false || empty($password) || $password !== $confirmPassword) {
        throw new Exception('Please fill in all fields correctly and ensure passwords match');
    }

    // Check password strength
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }

    // Register user
    $auth = new Auth($db->getPdo());
    if ($auth->register($username, $email, $password)) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! You can now log in.',
            'redirect' => '/login.php'
        ]);
    } else {
        throw new Exception('Registration failed. The email may already be registered.');
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('Registration error: ' . $e->getMessage());
    echo json_encode(['error' => 'Registration failed. Please try again.']);
}

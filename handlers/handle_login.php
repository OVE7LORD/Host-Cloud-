<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json');

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Get and validate input
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate input
    if ($email === false || empty($password)) {
        throw new Exception('Invalid email or password');
    }

    // Attempt login
    $auth = new Auth($db->getPdo());
    if ($auth->login($email, $password)) {
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (86400 * 30); // 30 days
            setcookie('remember_token', $token, [
                'expires' => $expires,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            // In a production environment, you would store the token in the database here
            // For now, we'll just set the cookie without database storage
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '/dashboard.php' // Redirect to dashboard after login
        ]);
    } else {
        throw new Exception('Invalid email or password');
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    echo json_encode(['error' => 'Login failed. Please try again.']);
}

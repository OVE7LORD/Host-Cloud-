<?php
session_start();
require_once 'config/Database.php';
require_once 'models/User.php';
require_once 'controllers/AuthController.php';

$auth = new AuthController();

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Если форма отправлена, обрабатываем вход
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth-style.css">
</head>
<body>
    <div class="auth-container">
        <h2 class="auth-header">Login</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                       required class="form-control" 
                       placeholder="Enter email">
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" 
                       required class="form-control" 
                       placeholder="Enter password">
            </div>
            
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>
        
        <p class="auth-link">Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>

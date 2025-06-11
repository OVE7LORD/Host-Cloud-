<?php
session_start();
require_once '../controllers/AuthController.php';

// Создаем экземпляр контроллера
$auth = new AuthController();

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Если форма отправлена, обрабатываем регистрацию
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->register();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth-style.css">
</head>
<body>
    <div class="auth-container">
        <h2 class="auth-header">Register</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                       required class="form-control" 
                       placeholder="Enter username">
            </div>
            
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
            
            <button type="submit" name="register" class="btn btn-primary">Register</button>
        </form>
        
        <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>



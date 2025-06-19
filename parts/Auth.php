<?php
declare(strict_types=1);

namespace HostCloud\Parts;

use PDO;
use PDOException;

class Auth
{
    private PDO $db;
    private array $errors = [];
    private string $table = 'users';

    public function __construct(PDO $db)
    {
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Validate user registration data
     */
    private function validateRegistration(string $username, string $email, string $password, string $confirmPassword = null): bool
    {
        $this->errors = [];

        // Validate username
        if (empty($username)) {
            $this->errors[] = 'Имя пользователя обязательно';
        } elseif (strlen($username) < 3) {
            $this->errors[] = 'Имя пользователя должно содержать минимум 3 символа';
        } elseif ($this->isUsernameExists($username)) {
            $this->errors[] = 'Имя пользователя уже занято';
        }

        // Validate email
        if (empty($email)) {
            $this->errors[] = 'Email обязателен';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Неверный формат email';
        } elseif ($this->isEmailExists($email)) {
            $this->errors[] = 'Email уже зарегистрирован';
        }

        // Validate password
        if (empty($password)) {
            $this->errors[] = 'Пароль обязателен';
        } elseif (strlen($password) < 6) {
            $this->errors[] = 'Пароль должен содержать минимум 6 символов';
        } elseif ($confirmPassword !== null && $password !== $confirmPassword) {
            $this->errors[] = 'Пароли не совпадают';
        }

        return empty($this->errors);
    }

    /**
     * Check if username exists
     */
    private function isUsernameExists(string $username): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return (bool)$stmt->fetch();
    }

    /**
     * Check if email exists
     */
    private function isEmailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetch();
    }

    /**
     * Register a new user
     */
    public function register(string $username, string $email, string $password, string $confirmPassword = null): bool
    {
        if (!$this->validateRegistration($username, $email, $password, $confirmPassword)) {
            return false;
        }

        try {
            $query = "INSERT INTO {$this->table} (username, email, password, created_at) 
                     VALUES (:username, :email, :password, NOW())";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'username' => htmlspecialchars(strip_tags($username)),
                'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            if ($result) {
                return $this->login($email, $password);
            }

            $this->errors[] = 'Ошибка при регистрации. Пожалуйста, попробуйте снова.';
            return false;
            
        } catch (PDOException $e) {
            $this->errors[] = 'Ошибка базы данных: ' . $e->getMessage();
            error_log('Registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Login user
     */
    public function login(string $email, string $password): bool
    {
        if (empty($email) || empty($password)) {
            $this->errors[] = 'Email и пароль обязательны';
            return false;
        }

        try {
            $query = "SELECT * FROM {$this->table} WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                return true;
            }
            
            $this->errors[] = 'Неверный email или пароль';
            return false;
            
        } catch (PDOException $e) {
            $this->errors[] = 'Ошибка входа. Пожалуйста, попробуйте позже.';
            error_log('Login error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        // Unset all session variables
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     */
    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current username
     */
    public function getUsername(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get last error
     */
    public function getLastError(): ?string
    {
        return end($this->errors) ?: null;
    }
}
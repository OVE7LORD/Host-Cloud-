<?php
// подключаем необходимые файлы
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/User.php';

class AuthController {
    private $db;      // подключение к базе
    private $user;    // объект пользователя

    // создаем объект контроллера
    public function __construct() {
        $this->db = (new Database())->connect();
        $this->user = new User($this->db);
    }

    // обрабатываем вход пользователя
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // получаем данные из формы
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // проверяем заполненность полей
            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Please fill in all fields';
                header('Location: login.php');
                exit;
            }

            // проверяем данные пользователя
            $this->user->email = $email;
            $this->user->password = $password;

            if ($this->user->login()) {
                // создаем сессию
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                header('Location: index.php');
                exit;
            } else {
                // обрабатываем ошибки
                $errors = $this->user->getErrors();
                $_SESSION['error'] = !empty($errors) ? implode('<br>', $errors) : 'Invalid email or password';
                header('Location: login.php');
                exit;
            }
        }
        include 'views/login.php';
    }

    // обрабатываем регистрацию
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // получаем данные из формы
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // проверяем заполненность полей
            if (empty($username) || empty($email) || empty($password)) {
                $_SESSION['error'] = 'Please fill in all fields';
                header('Location: register.php');
                exit;
            }

            // создаем нового пользователя
            $this->user->username = $username;
            $this->user->email = $email;
            $this->user->password = $password;

            if ($this->user->register()) {
                // создаем сессию
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                header('Location: index.php');
                exit;
            } else {
                // обрабатываем ошибки
                $errors = $this->user->getErrors();
                $_SESSION['error'] = !empty($errors) ? implode('<br>', $errors) : 'Registration failed';
                header('Location: register.php');
                exit;
            }
        }
        include 'views/register.php';
    }

    // обрабатываем выход из системы
    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

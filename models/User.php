<?php
class User {
    private $db;          // подключение к базе
    private $table = 'users';  // таблица пользователей
    private $errors = [];    // массив для ошибок

    public $id;            // идентификатор
    public $username;      // имя пользователя
    public $email;         // email
    public $password;      // пароль
    public $created_at;    // дата создания

    // создаем объект пользователя
    public function __construct($db) {
        $this->db = $db;
    }

    // проверяем данные пользователя
    private function validate() {
        $this->errors = [];
        
        if (empty($this->username)) {
            $this->errors[] = 'Username is required';
        } else {
            // проверяем уникальность имени
            $stmt = $this->db->prepare('SELECT id FROM ' . $this->table . ' WHERE username = :username');
            $stmt->execute(['username' => $this->username]);
            if ($stmt->fetch()) {
                $this->errors[] = 'Username already exists';
            }
        }
        
        if (empty($this->email)) {
            $this->errors[] = 'Email is required';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid email format';
        } else {
            // проверяем уникальность email
            $stmt = $this->db->prepare('SELECT id FROM ' . $this->table . ' WHERE email = :email');
            $stmt->execute(['email' => $this->email]);
            if ($stmt->fetch()) {
                $this->errors[] = 'Email already registered';
            }
        }
        
        if (empty($this->password)) {
            $this->errors[] = 'Password is required';
        } elseif (strlen($this->password) < 6) {
            $this->errors[] = 'Password must be at least 6 characters';
        }
        
        return empty($this->errors);
    }

    // получаем список ошибок
    public function getErrors() {
        return $this->errors;
    }

    // регистрируем нового пользователя
    public function register() {
        if (!$this->validate()) {
            return false;
        }

        try {
            $query = 'INSERT INTO ' . $this->table . ' 
                        (username, email, password, created_at)
                        VALUES (:username, :email, :password, :created_at)';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'username' => $this->username,
                'email' => $this->email,
                'password' => password_hash($this->password, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // получаем id нового пользователя
            $this->id = $this->db->lastInsertId();
            $this->username = $this->username;
            return true;
        } catch(PDOException $e) {
            $this->errors[] = 'Registration failed: ' . $e->getMessage();
            return false;
        }
    }

    // авторизуем пользователя
    public function login() {
        try {
            $query = 'SELECT * FROM ' . $this->table . ' WHERE email = :email';
            $stmt = $this->db->prepare($query);
            $stmt->execute(['email' => $this->email]);
            $user = $stmt->fetch();

            if ($user && password_verify($this->password, $user['password'])) {
                $this->id = $user['id'];
                $this->username = $user['username'];
                return true;
            }
            $this->errors[] = 'Invalid credentials';
            return false;
        } catch(PDOException $e) {
            $this->errors[] = 'Login failed: ' . $e->getMessage();
            return false;
        }
    }
}

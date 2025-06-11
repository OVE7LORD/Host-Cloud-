<?php
// Начинаем сессию
session_start();

// Очищаем все данные сессии
$_SESSION = [];

// Удаляем куки сессии
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Разрушаем сессию
session_destroy();

// Перенаправляем пользователя на страницу входа
header('Location: login.php');
exit;
?>

<?php
require_once 'db.php';

// Удаляем токен из базы, если есть кука remember_token
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("DELETE FROM user_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    // Удаляем куку remember_token
    setcookie('remember_token', '', time() - 3600, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
}

// Очищаем сессию
$_SESSION = array();

// Удаляем сессионную куку
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на главную
header('Location: index.php');
exit;
?>
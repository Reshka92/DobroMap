<?php
require_once 'config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(!$conn){
    die("Ошибка подключения:" . mysqli_connect_error());
}
if (session_status() === PHP_SESSION_NONE && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    $stmt = $conn->prepare("SELECT user_id, expires_at FROM user_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tokenData = $result->fetch_assoc();
        
        // Проверяем, не истек ли токен
        if ($tokenData['expires_at'] > time()) {
            // Получаем данные пользователя
            $userStmt = $conn->prepare("SELECT id, email, first_name, last_name FROM users WHERE id = ?");
            $userStmt->bind_param("i", $tokenData['user_id']);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            
            if ($userResult->num_rows > 0) {
                $user = $userResult->fetch_assoc();
                
                // Устанавливаем сессию
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['isLoggedIn'] = true;
                
                // Обновляем время жизни токена
                $newExpires = time() + (30 * 24 * 60 * 60);
                $updateStmt = $conn->prepare("UPDATE user_tokens SET expires_at = ? WHERE token = ?");
                $updateStmt->bind_param("is", $newExpires, $token);
                $updateStmt->execute();
                
                // Обновляем cookie
                setcookie('remember_token', $token, $newExpires, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
            }
        } else {
            // Удаляем просроченный токен
            $deleteStmt = $conn->prepare("DELETE FROM user_tokens WHERE token = ?");
            $deleteStmt->bind_param("s", $token);
            $deleteStmt->execute();
            
            // Удаляем cookie
            setcookie('remember_token', '', time() - 3600, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
        }
    }
}
if(session_status() === PHP_SESSION_NONE){
    // Устанавливаем параметры сессии
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => COOKIE_LIFETIME,
        'path' => COOKIE_PATH,
        'domain' => COOKIE_DOMAIN,
        'secure' => COOKIE_SECURE,
        'httponly' => COOKIE_HTTPONLY,
        'samesite' => 'Lax'
    ]);
    
    // Устанавливаем время жизни сессии на сервере
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
    ini_set('session.cookie_lifetime', COOKIE_LIFETIME);
    
    session_start();
    
    // Обновляем время жизни cookie сессии
    if (isset($_COOKIE[SESSION_NAME])) {
        setcookie(
            SESSION_NAME,
            $_COOKIE[SESSION_NAME],
            time() + COOKIE_LIFETIME,
            COOKIE_PATH,
            COOKIE_DOMAIN,
            COOKIE_SECURE,
            COOKIE_HTTPONLY
        );
    }
}
?>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$remember = $data['remember'] ?? false; // Добавляем опцию "Запомнить меня"

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email и пароль обязательны']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, email, password_hash, first_name, last_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Пользователь с таким email не найден']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['isLoggedIn'] = true;
        
        // Если пользователь выбрал "Запомнить меня"
        if ($remember) {
            // Создаем токен для длительной сессии
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60); // 30 дней
            
            // Сохраняем токен в базе данных
            $stmt = $conn->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $user['id'], $token, $expires);
            $stmt->execute();
            
            // Устанавливаем cookie с токеном
            setcookie('remember_token', $token, $expires, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Вход выполнен успешно',
            'redirect' => '../index.php'
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Неверный пароль']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера: ' . $e->getMessage()]);
}

$conn->close();
?>
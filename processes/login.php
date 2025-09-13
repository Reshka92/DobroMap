<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email и пароль обязательны']);
    exit;
}

try {
    // Исправлено: используем password_hash вместо password
    $stmt = $conn->prepare("SELECT id, email, password_hash, first_name, last_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Пользователь с таким email не найден']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // Исправлено: проверяем password_hash вместо password
    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['isLoggedIn'] = true;
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
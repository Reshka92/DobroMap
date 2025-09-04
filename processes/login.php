<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
require_once '../includes/db.php'; // Убедись, что путь корректный

try {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Неверный формат JSON");
    }

    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception("Email и пароль обязательны");
    }

    // Проверка пользователя
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        throw new Exception("Пользователь с таким email не найден");
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception("Неверный пароль");
    }
    $_SESSION['isLoggedIn'] = true;

    // Успешный вход
    echo json_encode([
        "success" => true,
        "redirect" => "../index.php",
        $isLoggedIn = true
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}

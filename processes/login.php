<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
<<<<<<< HEAD
header('Content-Type: application/json');
session_start();

// Подключаем ваши файлы конфигурации
require_once '../includes/config.php';
require_once '../includes/db.php';

try {
    // Проверяем существование таблицы markers
    $checkTable = $conn->query("SHOW TABLES LIKE 'markers'");
    if ($checkTable->num_rows == 0) {
        echo json_encode([]);
        exit;
    }

    // Загружаем метки с информацией о пользователе
    $query = "
        SELECT m.*, u.first_name, u.last_name 
        FROM markers m 
        LEFT JOIN users u ON m.user_id = u.id 
        ORDER BY m.created_at DESC
    ";
    
    $result = $conn->query($query);
    
    $markers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $markers[] = [
                'id' => $row['id'],
                'lat' => (float)$row['lat'],
                'lon' => (float)$row['lon'],
                'description' => $row['description'],
                'people_needed' => (int)$row['people_needed'],
                'event_date' => $row['event_date'],
                'event_time' => $row['event_time'],
                'created_at' => $row['created_at'],
                'user_name' => $row['first_name'] . ' ' . $row['last_name']
            ];
        }
        $result->free();
    }
    
    echo json_encode($markers);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
=======

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
>>>>>>> 2c1932761a0afedd9e492c736a340a7f057dbd30

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';

// Отладочная информация
error_log("=== SAVE MARKER DEBUG ===");
error_log("Session data: " . print_r($_SESSION, true));
error_log("POST raw: " . file_get_contents('php://input'));

// Проверка авторизации
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
    error_log("Ошибка: Пользователь не авторизован");
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

// Получение данных
$input = json_decode(file_get_contents('php://input'), true);
error_log("Decoded input: " . print_r($input, true));

if (!$input) {
    error_log("Ошибка: Некорректные JSON данные");
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

// Валидация данных
$required = ['lat', 'lon', 'desc', 'people', 'date', 'time'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        error_log("Ошибка: Не заполнено поле: $field");
        echo json_encode(['success' => false, 'message' => "Не заполнено поле: $field"]);
        exit;
    }
}

// Получаем user_id из сессии
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    error_log("Ошибка: user_id не найден в сессии");
    echo json_encode(['success' => false, 'message' => 'Ошибка авторизации']);
    exit;
}

// Сохранение в базу данных
try {
    // Создаем таблицу markers если не существует
    $conn->query("
        CREATE TABLE IF NOT EXISTS markers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            lat DECIMAL(10, 8) NOT NULL,
            lon DECIMAL(11, 8) NOT NULL,
            description TEXT NOT NULL,
            people_needed INT NOT NULL,
            event_date DATE NOT NULL,
            event_time TIME NOT NULL,
            status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Подготовленный запрос
    $stmt = $conn->prepare("
        INSERT INTO markers (user_id, lat, lon, description, people_needed, event_date, event_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("idddiss", 
        $user_id,
        $input['lat'],
        $input['lon'],
        $input['desc'], // Проверяем это значение
        $input['people'],
        $input['date'],
        $input['time']
    );
    
    if ($stmt->execute()) {
        $marker_id = $stmt->insert_id;
        error_log("Метка успешно сохранена, ID: $marker_id, Описание: " . $input['desc']);
        echo json_encode(['success' => true, 'message' => 'Метка успешно сохранена']);
    } else {
        error_log("Ошибка сохранения: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Ошибка сохранения: ' . $conn->error]);
    }
    
} catch (Exception $e) {
    error_log("Исключение: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}

$conn->close();
?>
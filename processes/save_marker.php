<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';

// Проверка авторизации
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

// Получение данных
$input = json_decode(file_get_contents('php://input'), true);
error_log("Received data: " . print_r($input, true));
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

// Валидация данных
$required = ['lat', 'lon', 'desc', 'people', 'date', 'time'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Не заполнено поле: $field"]);
        exit;
    }
}

// Получаем user_id из сессии
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Ошибка авторизации']);
    exit;
}

// Сохранение в базу данных
try {
    $stmt = $conn->prepare("
        INSERT INTO markers (user_id, lat, lon, description, people_needed, event_date, event_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    // ИСПРАВЛЕНО: изменен формат строки с "idddiss" на "iddsiss"
    $stmt->bind_param("iddsiss", 
        $user_id,
        $input['lat'],
        $input['lon'],
        $input['desc'], // Это строка, поэтому 's'
        $input['people'],
        $input['date'],
        $input['time']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Метка успешно сохранена']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка сохранения: ' . $conn->error]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}

$conn->close();
?>
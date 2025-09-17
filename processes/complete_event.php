<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

// Получаем входные данные
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные JSON']);
    exit;
}

$event_id = $input['event_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Не указано мероприятие']);
    exit;
}

// Логируем попытку завершения события
error_log("Попытка завершения события: event_id=$event_id, user_id=$user_id");

try {
    // Проверяем, является ли пользователь организатором события
    $check = $conn->prepare("SELECT user_id, status FROM markers WHERE id = ?");
    $check->bind_param("i", $event_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Событие не найдено: event_id=$event_id");
        echo json_encode(['success' => false, 'message' => 'Мероприятие не найдено']);
        exit;
    }
    
    $event = $result->fetch_assoc();
    
    // Только организатор может завершить мероприятие
    if ($event['user_id'] != $user_id) {
        error_log("Пользователь не является организатором: user_id=$user_id, organizer_id=" . $event['user_id']);
        echo json_encode(['success' => false, 'message' => 'Только организатор может завершить мероприятие']);
        exit;
    }
    
    // Проверяем, не завершено ли уже мероприятие
    if ($event['status'] === 'completed') {
        error_log("Мероприятие уже завершено: event_id=$event_id");
        echo json_encode(['success' => false, 'message' => 'Мероприятие уже завершено']);
        exit;
    }
    
    // Обновляем статус мероприятия и время завершения
    $stmt = $conn->prepare("UPDATE markers SET status = 'completed', completed_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    
    if ($stmt->execute()) {
        error_log("Мероприятие успешно завершено: event_id=$event_id");
        echo json_encode(['success' => true, 'message' => 'Мероприятие завершено']);
    } else {
        error_log("Ошибка базы данных при завершении: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $conn->error]);
    }
    
} catch (Exception $e) {
    error_log("Ошибка сервера при завершении события: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера: ' . $e->getMessage()]);
}
?>
<?php
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$event_id = $input['event_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Не указано мероприятие']);
    exit;
}

try {
    // Проверяем, не является ли пользователь создателем мероприятия
    $checkCreator = $conn->prepare("
        SELECT user_id FROM markers WHERE id = ?
    ");
    $checkCreator->bind_param("i", $event_id);
    $checkCreator->execute();
    $creatorResult = $checkCreator->get_result();
    
    if ($creatorResult->num_rows > 0) {
        $creatorData = $creatorResult->fetch_assoc();
        if ($creatorData['user_id'] == $user_id) {
            echo json_encode(['success' => false, 'message' => 'Вы не можете присоединиться к своему мероприятию']);
            exit;
        }
    }
    
    // Проверяем существование мероприятия
    $check = $conn->prepare("
        SELECT people_needed 
        FROM markers WHERE id = ? AND (status = 'active' OR status IS NULL)
    ");
    $check->bind_param("i", $event_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Мероприятие не найдено']);
        exit;
    }
    
    $event = $result->fetch_assoc();
    
    // Проверяем количество участников
    $countCheck = $conn->prepare("
        SELECT COUNT(*) as current_count 
        FROM event_participants WHERE event_id = ?
    ");
    $countCheck->bind_param("i", $event_id);
    $countCheck->execute();
    $countResult = $countCheck->get_result();
    $countData = $countResult->fetch_assoc();
    
    if ($countData['current_count'] >= $event['people_needed']) {
        echo json_encode(['success' => false, 'message' => 'Все места заняты']);
        exit;
    }
    
    // Проверяем, не присоединился ли уже пользователь
    $checkJoin = $conn->prepare("SELECT id FROM event_participants WHERE event_id = ? AND user_id = ?");
    $checkJoin->bind_param("ii", $event_id, $user_id);
    $checkJoin->execute();
    
    if ($checkJoin->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Вы уже присоединились к этому мероприятию']);
        exit;
    }
    
    // Добавляем участника
    $stmt = $conn->prepare("INSERT INTO event_participants (event_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $event_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Вы присоединились']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка присоединения']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}
?>
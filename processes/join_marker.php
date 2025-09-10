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

// Получаем user_id из сессии
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Ошибка авторизации']);
    exit;
}

// Получение данных
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['marker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

$marker_id = (int)$input['marker_id'];

try {
    // Проверяем существование метки
    $stmt = $conn->prepare("SELECT people_needed FROM markers WHERE id = ?");
    $stmt->bind_param("i", $marker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Метка не найдена']);
        exit;
    }
    
    $marker = $result->fetch_assoc();
    $people_needed = $marker['people_needed'];
    
    // Проверяем, не присоединился ли уже пользователь
    $check_stmt = $conn->prepare("SELECT id FROM marker_participants WHERE marker_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $marker_id, $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Вы уже присоединились к этому мероприятию']);
        exit;
    }
    
    // Добавляем участника
    $insert_stmt = $conn->prepare("INSERT INTO marker_participants (marker_id, user_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $marker_id, $user_id);
    
    if ($insert_stmt->execute()) {
        // Получаем текущее количество участников
        $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM marker_participants WHERE marker_id = ?");
        $count_stmt->bind_param("i", $marker_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $participants_count = $count_result->fetch_assoc()['count'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Вы присоединились к мероприятию!',
            'participants_count' => $participants_count,
            'people_needed' => $people_needed
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка присоединения']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}

$conn->close();
?>
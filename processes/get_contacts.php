<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Не указано мероприятие']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT u.id, u.first_name, u.last_name, u.email, u.phone
        FROM users u
        INNER JOIN event_participants ep ON u.id = ep.user_id
        WHERE ep.event_id = ?
        UNION
        SELECT u.id, u.first_name, u.last_name, u.email, u.phone
        FROM users u
        INNER JOIN markers m ON u.id = m.user_id
        WHERE m.id = ?
    ");
    $stmt->bind_param("ii", $event_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $contacts = [];
    while ($row = $result->fetch_assoc()) {
        $contacts[] = [
            'id' => $row['id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'phone' => $row['phone']
        ];
    }
    
    echo json_encode(['success' => true, 'contacts' => $contacts]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}
?>
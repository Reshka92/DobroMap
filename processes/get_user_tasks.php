<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

$type = $_GET['type'] ?? 'current';

try {
    if ($type === 'current') {
        $stmt = $conn->prepare("
            SELECT m.*, u.first_name, u.last_name 
            FROM markers m 
            JOIN event_participants ep ON m.id = ep.event_id 
            JOIN users u ON m.user_id = u.id 
            WHERE ep.user_id = ? AND (m.status = 'active' OR m.status IS NULL)
            ORDER BY m.event_date ASC
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT m.*, u.first_name, u.last_name 
            FROM markers m 
            JOIN event_participants ep ON m.id = ep.event_id 
            JOIN users u ON m.user_id = u.id 
            WHERE ep.user_id = ? AND m.status = 'completed'
            ORDER BY m.event_date DESC
        ");
    }
    
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'id' => $row['id'],
            'description' => htmlspecialchars($row['description']),
            'people_needed' => $row['people_needed'],
            'people_joined' => $row['people_joined'],
            'event_date' => date('d.m.Y', strtotime($row['event_date'])),
            'event_time' => $row['event_time'],
            'organizer' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name'])
        ];
    }
    
    echo json_encode(['success' => true, 'tasks' => $tasks]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}
?>
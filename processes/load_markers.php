<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';

try {
    // Проверяем существование таблицы markers
    $checkTable = $conn->query("SHOW TABLES LIKE 'markers'");
    if ($checkTable->num_rows == 0) {
        echo json_encode([]);
        exit;
    }

    // Загружаем метки с информацией о пользователе и количеством участников
    $query = "
        SELECT m.*, 
               u.first_name, 
               u.last_name,
               COUNT(mp.id) as participants_count,
               CASE WHEN mp2.user_id IS NOT NULL THEN 1 ELSE 0 END as current_user_joined
        FROM markers m 
        LEFT JOIN users u ON m.user_id = u.id 
        LEFT JOIN marker_participants mp ON m.id = mp.marker_id
        LEFT JOIN marker_participants mp2 ON m.id = mp2.marker_id AND mp2.user_id = ?
        GROUP BY m.id
        ORDER BY m.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $user_id = $_SESSION['user_id'] ?? 0;
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $markers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $markers[] = [
                'id' => $row['id'],
                'lat' => (float)$row['lat'],
                'lon' => (float)$row['lon'],
                'description' => $row['description'],
                'people_needed' => (int)$row['people_needed'],
                'participants_count' => (int)$row['participants_count'],
                'current_user_joined' => (bool)$row['current_user_joined'],
                'event_date' => $row['event_date'],
                'event_time' => $row['event_time'],
                'created_at' => $row['created_at'],
                'user_name' => $row['first_name'] . ' ' . $row['last_name']
            ];
        }
    }
    
    echo json_encode($markers);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
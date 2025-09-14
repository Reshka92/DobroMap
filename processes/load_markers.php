<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';

try {
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $query = "
        SELECT 
            m.*, 
            u.first_name, 
            u.last_name,
            COALESCE(ep_count.participants, 0) as people_joined,
            EXISTS(
                SELECT 1 FROM event_participants 
                WHERE event_id = m.id AND user_id = ?
            ) as user_joined
        FROM markers m 
        LEFT JOIN users u ON m.user_id = u.id 
        LEFT JOIN (
            SELECT event_id, COUNT(*) as participants 
            FROM event_participants 
            GROUP BY event_id
        ) ep_count ON m.id = ep_count.event_id
        WHERE m.status = 'active' OR m.status IS NULL
        ORDER BY m.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $markers = [];
    while ($row = $result->fetch_assoc()) {
        $can_join = ($row['people_joined'] < $row['people_needed']) && !$row['user_joined'];
        $is_creator = ($row['user_id'] == $user_id); // Проверяем, является ли пользователь создателем
        
        $markers[] = [
            'id' => $row['id'],
            'lat' => (float)$row['lat'],
            'lon' => (float)$row['lon'],
            'description' => htmlspecialchars($row['description']),
            'people_needed' => (int)$row['people_needed'],
            'people_joined' => (int)$row['people_joined'],
            'event_date' => $row['event_date'],
            'event_time' => $row['event_time'],
            'created_at' => $row['created_at'],
            'user_name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
            'user_joined' => (bool)$row['user_joined'],
            'can_join' => $can_join,
            'is_creator' => $is_creator, // Добавляем информацию о создателе
            'creator_id' => (int)$row['user_id'] // ID создателя
        ];
    }
    
    echo json_encode($markers);
    
} catch (Exception $e) {
    echo json_encode([]);
}

$conn->close();
?>
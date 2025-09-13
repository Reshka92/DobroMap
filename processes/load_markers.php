<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';

try {
    // Создаем таблицы если не существуют
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

    $conn->query("
        CREATE TABLE IF NOT EXISTS event_participants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_participation (event_id, user_id)
        )
    ");

    $user_id = $_SESSION['user_id'] ?? 0;
    
    $query = "
        SELECT 
            m.*, 
            u.first_name, 
            u.last_name,
            COUNT(ep.user_id) as people_joined,
            EXISTS(SELECT 1 FROM event_participants WHERE event_id = m.id AND user_id = ?) as user_joined
        FROM markers m 
        LEFT JOIN users u ON m.user_id = u.id 
        LEFT JOIN event_participants ep ON m.id = ep.event_id
        WHERE m.status = 'active' OR m.status IS NULL
        GROUP BY m.id
        ORDER BY m.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $markers = [];
    while ($row = $result->fetch_assoc()) {
        $can_join = ($row['people_joined'] < $row['people_needed']) && !$row['user_joined'];
        
        $markers[] = [
            'id' => $row['id'],
            'lat' => (float)$row['lat'],
            'lon' => (float)$row['lon'],
            'description' => htmlspecialchars($row['description']),
            'people_needed' => (int)$row['people_needed'],
            'people_joined' => (int)$row['people_joined'], // Теперь это реальное количество!
            'event_date' => $row['event_date'],
            'event_time' => $row['event_time'],
            'created_at' => $row['created_at'],
            'user_name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
            'user_joined' => (bool)$row['user_joined'],
            'can_join' => $can_join
        ];
    }
    
    echo json_encode($markers);
    
} catch (Exception $e) {
    echo json_encode([]);
}

$conn->close();
?>
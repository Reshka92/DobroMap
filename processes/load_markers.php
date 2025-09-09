<?php
header('Content-Type: application/json');
session_start();

// Подключаем ваши файлы конфигурации
require_once '../config.php';
require_once '../db.php';

try {
    // Проверяем существование таблицы markers
    $checkTable = $conn->query("SHOW TABLES LIKE 'markers'");
    if ($checkTable->num_rows == 0) {
        echo json_encode([]);
        exit;
    }

    // Загружаем метки с информацией о пользователе
    $query = "
        SELECT m.*, u.first_name, u.last_name 
        FROM markers m 
        LEFT JOIN users u ON m.user_id = u.id 
        ORDER BY m.created_at DESC
    ";
    
    $result = $conn->query($query);
    
    $markers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $markers[] = [
                'id' => $row['id'],
                'lat' => (float)$row['lat'],
                'lon' => (float)$row['lon'],
                'description' => $row['description'],
                'people_needed' => (int)$row['people_needed'],
                'event_date' => $row['event_date'],
                'event_time' => $row['event_time'],
                'created_at' => $row['created_at'],
                'user_name' => $row['first_name'] . ' ' . $row['last_name']
            ];
        }
        $result->free();
    }
    
    echo json_encode($markers);
    
} catch (Exception $e) {
    echo json_encode([]);
}

$conn->close();
?>
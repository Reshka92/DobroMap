<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

// Отладочная информация
error_log("=== SAVE MARKER DEBUG ===");
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));
error_log("POST data: " . file_get_contents('php://input'));

// Подключаем ваши файлы конфигурации
require_once '../includes/config.php';
require_once '../includes/db.php';

// Проверка авторизации
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
    error_log("Ошибка: Пользователь не авторизован");
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

// Получение данных
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    error_log("Ошибка: Некорректные JSON данные");
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

error_log("Полученные данные: " . print_r($input, true));

// Валидация данных
$required = ['lat', 'lon', 'desc', 'people', 'date', 'time'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        error_log("Ошибка: Не заполнено поле: $field");
        echo json_encode(['success' => false, 'message' => 'Не все поля заполнены']);
        exit;
    }
}

// Получаем user_id из сессии
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    error_log("Ошибка: user_id не найден в сессии");
    echo json_encode(['success' => false, 'message' => 'Ошибка авторизации: user_id не найден']);
    exit;
}

error_log("User ID: $user_id");

// Сохранение в базу данных
try {
    // Проверяем существование таблицы markers
    $checkTable = $conn->query("SHOW TABLES LIKE 'markers'");
    if ($checkTable->num_rows == 0) {
        error_log("Создание таблицы markers");
        // Создаем таблицу
        $createTable = "
            CREATE TABLE markers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                lat DECIMAL(10, 8) NOT NULL,
                lon DECIMAL(11, 8) NOT NULL,
                description TEXT NOT NULL,
                people_needed INT NOT NULL,
                event_date DATE NOT NULL,
                event_time TIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        if ($conn->query($createTable)) {
            error_log("Таблица markers создана успешно");
        } else {
            error_log("Ошибка создания таблицы: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $conn->error]);
            exit;
        }
    }

    // Подготовленный запрос для безопасности
    $stmt = $conn->prepare("
        INSERT INTO markers (user_id, lat, lon, description, people_needed, event_date, event_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("idddiss", 
        $user_id,
        $input['lat'],
        $input['lon'],
        $input['desc'],
        $input['people'],
        $input['date'],
        $input['time']
    );
    
    if ($stmt->execute()) {
        $marker_id = $stmt->insert_id;
        error_log("Метка успешно сохранена, ID: $marker_id");
        echo json_encode([
            'success' => true, 
            'message' => 'Метка успешно сохранена',
            'marker_id' => $marker_id
        ]);
    } else {
        error_log("Ошибка сохранения: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Ошибка сохранения: ' . $conn->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Исключение: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}

$conn->close();
?>
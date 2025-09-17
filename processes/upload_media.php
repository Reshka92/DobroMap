<?php
// Очищаем буфер вывода
if (ob_get_length()) ob_clean();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Исправляем путь к логам
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();

// Проверяем существование папки logs
$log_dir = __DIR__ . '/../logs/';
if (!file_exists($log_dir)) {
    @mkdir($log_dir, 0755, true);
}

require_once '../includes/config.php';
require_once '../includes/db.php';

error_log("=== UPLOAD_MEDIA START ===");

if (!isset($_SESSION['user_id'])) {
    error_log("User not authenticated");
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

$event_id = $_POST['event_id'] ?? null;
$comment = $_POST['comment'] ?? '';

error_log("Event ID: " . $event_id);
error_log("Comment: " . $comment);

if (!$event_id) {
    error_log("No event_id provided");
    echo json_encode(['success' => false, 'message' => 'Не указано мероприятие']);
    exit;
}

if (empty($_FILES['media'])) {
    error_log("No files uploaded");
    echo json_encode(['success' => false, 'message' => 'Не выбраны файлы']);
    exit;
}

try {
    // Используем абсолютный путь
    $upload_dir = __DIR__ . '/../uploads/events/' . $event_id . '/';
    error_log("Upload directory: " . $upload_dir);
    
    // Проверяем базовые папки
    $base_uploads = __DIR__ . '/../uploads/';
    $base_events = __DIR__ . '/../uploads/events/';
    
    if (!file_exists($base_uploads)) {
        error_log("Creating base uploads directory: " . $base_uploads);
        if (!@mkdir($base_uploads, 0755, true)) {
            $error = error_get_last();
            error_log("Failed to create uploads directory: " . ($error['message'] ?? 'Unknown error'));
            echo json_encode(['success' => false, 'message' => 'Ошибка создания папки uploads']);
            exit;
        }
    }
    
    if (!file_exists($base_events)) {
        error_log("Creating events directory: " . $base_events);
        if (!@mkdir($base_events, 0755, true)) {
            $error = error_get_last();
            error_log("Failed to create events directory: " . ($error['message'] ?? 'Unknown error'));
            echo json_encode(['success' => false, 'message' => 'Ошибка создания папки events']);
            exit;
        }
    }
    
    if (!file_exists($upload_dir)) {
        error_log("Creating event directory: " . $upload_dir);
        if (!@mkdir($upload_dir, 0755, true)) {
            $error = error_get_last();
            error_log("Failed to create event directory: " . ($error['message'] ?? 'Unknown error'));
            echo json_encode(['success' => false, 'message' => 'Ошибка создания папки мероприятия. Проверьте права доступа.']);
            exit;
        }
    }
    
    // Проверяем права на запись
    if (!is_writable($upload_dir)) {
        error_log("Directory not writable: " . $upload_dir);
        // Пытаемся изменить права
        if (@chmod($upload_dir, 0755)) {
            error_log("Fixed permissions for: " . $upload_dir);
        } else {
            echo json_encode(['success' => false, 'message' => 'Нет прав на запись в папку']);
            exit;
        }
    }
    
    $uploaded_files = [];
    $upload_errors = [];
    
    foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['media']['error'][$key] !== UPLOAD_ERR_OK) {
            $error_msg = 'Ошибка загрузки файла: ' . $_FILES['media']['name'][$key] . ' (код: ' . $_FILES['media']['error'][$key] . ')';
            $upload_errors[] = $error_msg;
            error_log($error_msg);
            continue;
        }
        
        $file_name = $_FILES['media']['name'][$key];
        $file_size = $_FILES['media']['size'][$key];
        $file_type = $_FILES['media']['type'][$key];
        
        error_log("Processing file: " . $file_name . ", type: " . $file_type . ", size: " . $file_size);
        
        // Проверка типа файла
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/mpeg'];
        if (!in_array($file_type, $allowed_types)) {
            $upload_errors[] = 'Недопустимый тип файла: ' . $file_name;
            continue;
        }
        
        // Проверка размера файла (максимум 10MB)
        if ($file_size > 10 * 1024 * 1024) {
            $upload_errors[] = 'Файл слишком большой: ' . $file_name;
            continue;
        }
        
        // Генерация уникального имени файла
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_name = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $unique_name;
        
        error_log("Moving file to: " . $file_path);
        
        if (move_uploaded_file($tmp_name, $file_path)) {
            $file_type_category = strpos($file_type, 'image/') === 0 ? 'image' : 'video';
            
            $stmt = $conn->prepare("
                INSERT INTO event_media (event_id, user_id, file_path, file_type, comment)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            if ($stmt) {
                // Используем относительный путь для базы данных
                $db_file_path = 'uploads/events/' . $event_id . '/' . $unique_name;
                
                $stmt->bind_param("iisss", $event_id, $_SESSION['user_id'], $db_file_path, $file_type_category, $comment);
                
                if ($stmt->execute()) {
                    error_log("File saved to database: " . $db_file_path);
                    $uploaded_files[] = [
                        'name' => $file_name,
                        'path' => $db_file_path,
                        'type' => $file_type_category
                    ];
                } else {
                    $db_error = $conn->error;
                    error_log("Database error: " . $db_error);
                    $upload_errors[] = 'Ошибка базы данных для файла: ' . $file_name;
                    
                    // Удаляем файл если не удалось сохранить в БД
                    @unlink($file_path);
                }
                
                $stmt->close();
            } else {
                error_log("Prepare statement failed: " . $conn->error);
                $upload_errors[] = 'Ошибка подготовки запроса для файла: ' . $file_name;
            }
        } else {
            $error = error_get_last();
            error_log("Failed to move uploaded file: " . $file_name . " - " . ($error['message'] ?? 'Unknown error'));
            $upload_errors[] = 'Ошибка перемещения файла: ' . $file_name;
        }
    }
    
    if (count($uploaded_files) > 0) {
        error_log("Successfully uploaded " . count($uploaded_files) . " files");
        echo json_encode([
            'success' => true, 
            'message' => 'Файлы загружены', 
            'files' => $uploaded_files,
            'errors' => $upload_errors
        ]);
    } else {
        error_log("No files were uploaded successfully");
        echo json_encode([
            'success' => false, 
            'message' => 'Не удалось загрузить файлы. ' . implode(', ', $upload_errors),
            'errors' => $upload_errors
        ]);
    }
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка загрузки: ' . $e->getMessage()
    ]);
}

error_log("=== UPLOAD_MEDIA END ===");
?>
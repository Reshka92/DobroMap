<?php
session_start();
date_default_timezone_set('Europe/Moscow');

require_once 'includes/db.php';

// Проверяем авторизацию
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
    header('Location: login.php');
    exit;
}

// Получаем данные пользователя
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Функция для получения инициалов
function getInitials($name) {
    $names = explode(' ', $name);
    $initials = '';
    foreach ($names as $n) {
        if (!empty(trim($n))) {
            $initials .= strtoupper(substr(trim($n), 0, 1));
        }
    }
    return $initials;
}

// Получаем выполненные задачи пользователя
$completed_tasks = [];

try {
    if (!$conn || $conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных");
    }
    
    $query = "
        SELECT m.*, u.first_name, u.last_name 
        FROM markers m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.status = 'completed' 
        AND (m.user_id = ? OR EXISTS (
            SELECT 1 FROM event_participants ep 
            WHERE ep.event_id = m.id AND ep.user_id = ?
        ))
        ORDER BY m.completed_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Ошибка подготовки запроса");
    }
    
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Получаем медиафайлы для этой задачи
        $media_files = [];
        $media_query = "SELECT * FROM event_media WHERE event_id = ? ORDER BY uploaded_at DESC";
        $media_stmt = $conn->prepare($media_query);
        
        if ($media_stmt) {
            $media_stmt->bind_param("i", $row['id']);
            $media_stmt->execute();
            $media_result = $media_stmt->get_result();
            
            while ($media_row = $media_result->fetch_assoc()) {
                $media_files[] = $media_row;
            }
            $media_stmt->close();
        }
        
        $row['media_files'] = $media_files;
        $completed_tasks[] = $row;
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $error_message = "Ошибка при загрузке данных";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выполненные дела | TaskManager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/4.css">
</head>
<body>
    <div class="app-container">
        <!-- Боковая панель - всегда видима -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="app-logo">
                    <i class="fas fa-tasks"></i>
                    <span>TaskManager</span>
                </div>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar large">
                    <?php echo getInitials($user_name); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user_name); ?></h3>
                    <p><?php echo htmlspecialchars($user_email); ?></p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="profile.php" class="nav-item">
                    <i class="fas fa-list-check"></i>
                    <span>Текущие дела</span>
                </a>
                <a href="#" class="nav-item active">
                    <i class="fas fa-check-circle"></i>
                    <span>Выполненные дела</span>
                </a>
                <a href="leaders.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i> 
                    <span>Лидеры</span>
                </a>
                <a href="index.php" class="nav-item">
                    <i class="fas fa-map"></i>
                    <span>На карту</span>
                </a>
                
                <a href="processes/logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Выйти</span>
                </a>
            </nav>
        </div>
        
        <!-- Основной контент -->
        <div class="main-content">
            <header class="content-header">
                <h1>Выполненные дела</h1>
                <div class="header-actions">
                    <div class="user-avatar small"><?php echo getInitials($user_name); ?></div>
                </div>
            </header>
            
            <div class="content-body">
                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (count($completed_tasks) > 0): ?>
                    <div class="tasks-grid">
                        <?php foreach ($completed_tasks as $task): ?>
                            <div class="task-card">
                                <div class="task-card-header">
                                    <h3><?php echo htmlspecialchars($task['description']); ?></h3>
                                    <span class="task-status status-completed">
                                        <i class="fas fa-check-circle"></i> Завершено
                                    </span>
                                </div>
                                
                                <div class="task-info">
                                    <div class="info-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('d.m.Y', strtotime($task['event_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $task['event_time']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></span>
                                    </div>
                                    <?php if ($task['completed_at']): ?>
                                    <div class="task-completion-info">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Завершено: <?php echo date('d.m.Y H:i', strtotime($task['completed_at'])); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($task['media_files'])): ?>
                                <div class="task-media">
                                    <h4><i class="fas fa-images"></i> Материалы отчета</h4>
                                    <div class="media-gallery">
                                        <?php foreach ($task['media_files'] as $media): ?>
                                            <div class="media-item">
                                                <?php if ($media['file_type'] == 'image'): ?>
                                                    <img src="<?php echo $media['file_path']; ?>" 
                                                         alt="Отчетное изображение" 
                                                         onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <video controls>
                                                        <source src="<?php echo $media['file_path']; ?>" type="video/mp4">
                                                    </video>
                                                    <div class="video-overlay">
                                                        <i class="fas fa-play-circle"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if (!empty($task['media_files'][0]['comment'])): ?>
                                    <div class="media-comment">
                                        <strong>Описание выполненной работы:</strong>
                                        <p><?php echo htmlspecialchars($task['media_files'][0]['comment']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <div class="no-media">
                                    <i class="fas fa-info-circle"></i>
                                    <p>К этому делу не прикреплены материалы отчета.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>Нет выполненных задач</h3>
                        <p>У вас пока нет завершенных дел. Завершите текущие задачи, чтобы они появились здесь.</p>
                        <a href="profile.php" class="btn-primary">Вернуться к текущим делам</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Обработка видео
        document.addEventListener('DOMContentLoaded', function() {
            const videoItems = document.querySelectorAll('.media-item video');
            videoItems.forEach(video => {
                const overlay = video.parentElement.querySelector('.video-overlay');
                if (overlay) {
                    video.addEventListener('play', function() {
                        overlay.style.display = 'none';
                    });
                    video.addEventListener('pause', function() {
                        overlay.style.display = 'flex';
                    });
                    video.addEventListener('ended', function() {
                        overlay.style.display = 'flex';
                    });
                }
            });
        });
    </script>
</body>
</html>
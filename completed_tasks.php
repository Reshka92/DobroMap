<?php
// completed_tasks.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Настройка логирования
$log_file = __DIR__ . '/../logs/debug_completed_tasks.log';
if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0755, true);
}
ini_set('error_log', $log_file);

session_start();
date_default_timezone_set('Europe/Moscow');

require_once 'includes/db.php';

// Проверяем авторизацию
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
    error_log("Пользователь не авторизован, перенаправление на login.php");
    header('Location: login.php');
    exit;
}

// Получаем данные пользователя
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

error_log("=== НАЧАЛО ОБРАБОТКИ completed_tasks.php ===");
error_log("User ID: $user_id, Name: $user_name");

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
    // Проверяем соединение с базой данных
    if (!$conn || $conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . ($conn ? $conn->connect_error : "Нет соединения"));
    }
    
    // Альтернативный запрос - проверяем оба варианта
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
    
    error_log("Выполняем запрос для user_id: " . $user_id);
    error_log("SQL запрос: " . $query);
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Ошибка подготовки запроса: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $user_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $num_rows = $result->num_rows;
    error_log("Найдено записей в базе: " . $num_rows);
    
    // Проверяем, есть ли вообще завершенные мероприятия в системе
    $check_completed = $conn->query("SELECT COUNT(*) as total FROM markers WHERE status = 'completed'");
    if ($check_completed) {
        $total_completed = $check_completed->fetch_assoc()['total'];
        error_log("Всего завершенных мероприятий в системе: " . $total_completed);
    }
    
    // Проверяем, есть ли пользователь в таблице участников
    $check_participation = $conn->prepare("SELECT COUNT(*) as part_count FROM event_participants WHERE user_id = ?");
    $check_participation->bind_param("i", $user_id);
    $check_participation->execute();
    $part_result = $check_participation->get_result();
    $part_count = $part_result->fetch_assoc()['part_count'];
    error_log("Пользователь участвует в мероприятиях: " . $part_count);
    
    while ($row = $result->fetch_assoc()) {
        error_log("Найдена задача: " . $row['id'] . " - " . $row['description'] . " - Организатор: " . $row['user_id']);
        
        // Получаем медиафайлы для этой задачи
        $media_files = [];
        
        // ИСПРАВЛЕНИЕ: используем uploaded_at вместо created_at
        $media_query = "SELECT * FROM event_media WHERE event_id = ? ORDER BY uploaded_at DESC";
        $media_stmt = $conn->prepare($media_query);
        
        if ($media_stmt) {
            $media_stmt->bind_param("i", $row['id']);
            $media_stmt->execute();
            $media_result = $media_stmt->get_result();
            
            while ($media_row = $media_result->fetch_assoc()) {
                // Проверяем существование файла
                $file_path = $media_row['file_path'];
                if (file_exists($file_path)) {
                    $media_files[] = $media_row;
                } else {
                    error_log("Файл не найден: " . $file_path);
                    // Добавляем запись даже если файл не найден
                    $media_files[] = $media_row;
                }
            }
            
            $media_stmt->close();
        } else {
            error_log("Ошибка подготовки медиа-запроса: " . $conn->error);
        }
        
        $row['media_files'] = $media_files;
        $completed_tasks[] = $row;
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Ошибка при получении выполненных задач: " . $e->getMessage());
    $error_message = "Ошибка при загрузке данных: " . $e->getMessage();
}

// Отладочная информация
error_log("Найдено выполненных задач для отображения: " . count($completed_tasks));
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выполненные дела | TaskManager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/4.css">
    <style>
        .media-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .media-item {
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            height: 150px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .media-item:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .media-item img, .media-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.3);
            color: white;
            font-size: 3rem;
        }
        
        .media-comment {
            margin-top: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            border-left: 4px solid #4361ee;
        }
        
        .media-comment strong {
            display: block;
            margin-bottom: 5px;
            color: #212529;
        }
        
        .task-completion-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6c757d;
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .no-media {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .task-media {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .task-media h4 {
            margin-bottom: 15px;
            color: #4361ee;
            font-size: 16px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .empty-state p {
            margin-bottom: 30px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Стили для модального окна просмотра медиа */
        .media-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            align-items: center;
            justify-content: center;
        }
        
        .media-modal.active {
            display: flex;
        }
        
        .media-modal-content {
            max-width: 90%;
            max-height: 90%;
        }
        
        .media-modal-content img,
        .media-modal-content video {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
        }
        
        .close-media-modal {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .close-media-modal:hover {
            color: #f72585;
        }
        
        /* Адаптивность для медиа-галереи */
        @media (max-width: 768px) {
            .media-gallery {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
            
            .media-item {
                height: 120px;
            }
        }
        
        /* Стили для отладки */
        .debug-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 12px;
            color: while;
        }
        .sidebar-nav .nav-item .fa-chart-bar {
    color: white !important;
}

.sidebar-nav .nav-item.active .fa-chart-bar {
    color: white !important;
}

.sidebar-nav .nav-item:hover .fa-chart-bar {
    color: white !important;
}
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Боковая панель -->
        <div class="sidebar">
    <div class="sidebar-header">
        <button class="mobile-nav-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="app-logo">
            <i class="fas fa-tasks"></i>
            <span>TaskManager</span>
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
                <a href="index.php" class="nav-item" >
                <i class="fas fa-map" ></i>
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
                    
                    <div class="user-menu-mobile">
                        <div class="user-avatar small"><?php echo getInitials($user_name); ?></div>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <!-- Блок отладочной информации -->
                
                
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
                                            <div class="media-item" data-src="<?php echo $media['file_path']; ?>" data-type="<?php echo $media['file_type']; ?>">
                                                <?php if ($media['file_type'] == 'image'): ?>
                                                    <img src="<?php echo $media['file_path']; ?>" alt="Отчетное изображение" onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <video>
                                                        <source src="<?php echo $media['file_path']; ?>" type="video/mp4">
                                                    </video>
                                                    <div class="video-overlay">
                                                        <i class="fas fa-play-circle"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php 
                                    // Показываем комментарий к первому медиафайлу
                                    if (!empty($task['media_files'][0]['comment'])): 
                                    ?>
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

    <!-- Модальное окно для просмотра медиа -->
    <div class="media-modal" id="mediaModal">
        <span class="close-media-modal">&times;</span>
        <div class="media-modal-content" id="mediaModalContent">
            <!-- Контент будет добавлен через JavaScript -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Элементы модального окна
            const mediaModal = document.getElementById('mediaModal');
            const mediaModalContent = document.getElementById('mediaModalContent');
            const closeMediaModal = document.querySelector('.close-media-modal');
            
            // Открытие модального окна при клике на медиа-элемент
            const mediaItems = document.querySelectorAll('.media-item');
            
            mediaItems.forEach(item => {
                item.addEventListener('click', function() {
                    const src = this.getAttribute('data-src');
                    const type = this.getAttribute('data-type');
                    
                    if (type === 'image') {
                        mediaModalContent.innerHTML = `<img src="${src}" alt="Увеличенное изображение">`;
                    } else if (type === 'video') {
                        mediaModalContent.innerHTML = `
                            <video controls autoplay>
                                <source src="${src}" type="video/mp4">
                                Ваш браузер не поддерживает видео.
                            </video>
                        `;
                    }
                    
                    mediaModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            });
            
            // Закрытие модального окна
            closeMediaModal.addEventListener('click', function() {
                mediaModal.classList.remove('active');
                document.body.style.overflow = '';
                
                // Останавливаем видео при закрытии
                const video = mediaModalContent.querySelector('video');
                if (video) {
                    video.pause();
                }
            });
            
            // Закрытие модального окна при клике вне контента
            mediaModal.addEventListener('click', function(e) {
                if (e.target === mediaModal) {
                    mediaModal.classList.remove('active');
                    document.body.style.overflow = '';
                    
                    // Останавливаем видео при закрытии
                    const video = mediaModalContent.querySelector('video');
                    if (video) {
                        video.pause();
                    }
                }
            });
                
            // Закрытие модального окна клавишей Esc
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mediaModal.classList.contains('active')) {
                    mediaModal.classList.remove('active');
                    document.body.style.overflow = '';
                    
                    // Останавливаем видео при закрытии
                    const video = mediaModalContent.querySelector('video');
                    if (video) {
                        video.pause();
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php
error_log("=== ЗАВЕРШЕНИЕ ОБРАБОТКИ completed_tasks.php ===");
?>
<?php
session_start();
// Установка часового пояса
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

// Получаем список лидеров
$leaders = [];

try {
    // Запрос для получения пользователей с количеством завершенных дел (только тех, у кого есть завершенные дела)
    $stmt = $conn->prepare("
        SELECT 
            u.id, 
            u.first_name, 
            u.last_name, 
            COUNT(DISTINCT m.id) AS completed_count
        FROM users u
        LEFT JOIN markers m ON u.id = m.user_id AND m.status = 'completed'
        LEFT JOIN event_participants ep ON u.id = ep.user_id
        LEFT JOIN markers m2 ON ep.event_id = m2.id AND m2.status = 'completed'
        GROUP BY u.id
        HAVING completed_count > 0
        ORDER BY completed_count DESC
        LIMIT 50
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $position = 1;
    while ($row = $result->fetch_assoc()) {
        $row['position'] = $position++;
        $row['initials'] = getInitials($row['first_name'] . ' ' . $row['last_name']);
        $leaders[] = $row;
    }
} catch (Exception $e) {
    error_log("Ошибка при получении лидеров: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Таблица лидеров | TaskManager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/4.css">
    <style>
        .leaderboard {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
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
        
        .leader-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--light-gray);
            transition: var(--transition);
        }
        
        .leader-item:hover {
            background-color: #f9f9f9;
        }
        
        .leader-item:last-child {
            border-bottom: none;
        }
        
        .leader-position {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary);
            min-width: 40px;
            text-align: center;
        }
        
        .position-1 {
            color: gold;
            font-size: 28px;
        }
        
        .position-2 {
            color: silver;
            font-size: 26px;
        }
        
        .position-3 {
            color: #cd7f32; /* bronze */
            font-size: 26px;
        }
        
        .leader-avatar {
            margin: 0 15px;
        }
        
        .leader-info {
            flex-grow: 1;
        }
        
        .leader-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .leader-stats {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .completed-count {
            font-size: 18px;
            font-weight: bold;
            color: var(--success);
        }
        
        .completed-label {
            font-size: 14px;
            color: var(--gray);
        }
        
        .trophy-icon {
            color: gold;
            font-size: 20px;
            margin-right: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
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
        
        @media (max-width: 768px) {
            .leader-item {
                padding: 15px;
            }
            
            .leader-position {
                font-size: 20px;
                min-width: 30px;
            }
            
            .position-1, .position-2, .position-3 {
                font-size: 22px;
            }
            
            .completed-count {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Боковая панель -->
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
                <a href="completed_tasks.php" class="nav-item">
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
                <h1>Таблица лидеров</h1>
                <div class="header-actions">
                    <div class="user-menu-mobile">
                        <div class="user-avatar small"><?php echo getInitials($user_name); ?></div>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <?php if (count($leaders) > 0): ?>
                    <div class="leaderboard">
                        <?php foreach ($leaders as $leader): ?>
                            <div class="leader-item">
                                <div class="leader-position <?php echo 'position-' . $leader['position']; ?>">
                                    <?php echo $leader['position']; ?>
                                </div>
                                
                                <div class="leader-avatar">
                                    <div class="user-avatar small"><?php echo $leader['initials']; ?></div>
                                </div>
                                
                                <div class="leader-info">
                                    <div class="leader-name">
                                        <?php echo htmlspecialchars($leader['first_name'] . ' ' . $leader['last_name']); ?>
                                    </div>
                                    
                                    <div class="leader-stats">
                                        <div class="completed-count">
                                            <i class="fas fa-trophy trophy-icon"></i>
                                            <?php echo $leader['completed_count']; ?>
                                        </div>
                                        <div class="completed-label">завершенных дел</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-trophy"></i>
                        <h3>Пока нет лидеров</h3>
                        <p>Станьте первым, кто завершит доброе дело!</p>
                        <a href="index.php" class="btn-primary">Посмотреть на карте</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
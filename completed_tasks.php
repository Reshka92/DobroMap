<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выполненные дела - TaskManager</title>
    <link rel="stylesheet" href="css/3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --card-gradient: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            --accent-color: #ff6b6b;
            --text-dark: #2d3748;
            --text-light: #718096;
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px rgba(0, 0, 0, 0.15);
        }

        body {
            background: var(--primary-gradient);
            color: var(--text-dark);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .back-button i {
            margin-right: 8px;
        }

        .profile-container {
            display: flex;
            gap: 25px;
            margin-top: 20px;
        }

        .profile-sidebar {
            flex: 0 0 300px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            padding: 25px;
            height: fit-content;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .profile-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .user-info {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .user-info-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0 auto 15px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .user-info-avatar:hover {
            transform: scale(1.05) rotate(5deg);
        }

        .user-info-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .user-info-email {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-menu li {
            margin-bottom: 12px;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: var(--text-dark);
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.5);
        }

        .nav-menu a:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateX(5px);
        }

        .nav-menu a.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .nav-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            padding: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 1.8rem;
            font-weight: bold;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .tasks-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }

        .task-card {
            border-radius: 15px;
            padding: 0;
            overflow: hidden;
            transition: all 0.4s ease;
            background: var(--card-gradient);
            box-shadow: var(--shadow-sm);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .task-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .task-card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .task-card:nth-child(3) {
            animation-delay: 0.2s;
        }

        .task-card:nth-child(4) {
            animation-delay: 0.3s;
        }

        .completed-task-card {
            border-left: 5px solid #4facfe;
        }

        .task-header-completed {
            background: var(--success-gradient);
            color: white;
            padding: 20px;
            margin: 0;
        }

        .task-title {
            font-weight: bold;
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .task-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.3);
        }

        .status-completed {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .task-content {
            padding: 20px;
        }

        .task-completed-date {
            font-size: 0.9rem;
            color: #4facfe;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .task-completed-date i {
            margin-right: 8px;
        }

        .task-deadline {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .task-deadline i {
            margin-right: 8px;
        }

        .task-description {
            font-size: 0.95rem;
            margin-bottom: 15px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
        }

        .task-description i {
            margin-right: 8px;
            color: #4facfe;
        }

        .task-members {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            font-weight: bold;
            margin-right: -10px;
            border: 2px solid white;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .member-avatar:hover {
            transform: translateY(-3px);
            z-index: 2;
        }

        .media-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .media-section h4 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
        }

        .media-section h4 i {
            margin-right: 10px;
            color: #4facfe;
        }

        .media-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .media-item {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .media-item:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        .media-item:hover::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.1);
        }

        .media-item img, .media-item video {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }

        .media-comment {
            padding: 12px;
            background: white;
            font-size: 0.85rem;
            color: var(--text-dark);
        }

        .media-date {
            font-size: 0.75rem;
            color: var(--text-light);
            padding: 8px 12px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
        }

        .media-date i {
            margin-right: 5px;
        }

        .no-media {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
            font-style: italic;
            background: rgba(0, 0, 0, 0.03);
            border-radius: 12px;
        }

        .no-media i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
            color: #cbd5e0;
        }

        /* Модальное окно */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.active {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            max-width: 90%;
            max-height: 80%;
            margin: auto;
            display: block;
            border-radius: 8px;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
        }

        .close {
            position: absolute;
            top: 25px;
            right: 35px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }

        /* Анимации */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        /* Адаптивность */
        @media (max-width: 1024px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-sidebar {
                flex: 1;
                margin-bottom: 20px;
            }
            
            .tasks-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .media-gallery {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">TaskManager</div>
            <div class="user-menu">
                <div class="user-avatar"><?php echo getInitials($user_name); ?></div>
            </div>
        </div>
    </header>

    <div class="container">
        <a href="profile.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Назад к профилю
        </a>
        
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="user-info">
                    <div class="user-info-avatar pulse"><?php echo getInitials($user_name); ?></div>
                    <div class="user-info-name"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="user-info-email"><?php echo htmlspecialchars($user_email); ?></div>
                </div>
                
                <ul class="nav-menu">
                    <li><a href="profile.php"><i class="fas fa-tasks"></i> Текущие дела</a></li>
                    <li><a href="#" class="active"><i class="fas fa-check-circle"></i> Выполненные дела</a></li>
                    <li><a href="processes/logout.php" style="color: #ff4444;"><i class="fas fa-sign-out-alt"></i> Выйти</a></li>
                </ul>
            </div>
            
            <div class="profile-main">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Выполненные дела</div>
                        <div class="task-count"><?php echo count($completed_tasks); ?> завершённых задач</div>
                    </div>
                    
                    <div class="tasks-container" id="tasksContainer">
                        <?php if (count($completed_tasks) > 0): ?>
                            <?php foreach ($completed_tasks as $task): ?>
                                <div class="task-card completed-task-card">
                                    <div class="task-header-completed">
                                        <div class="task-title"><?php echo htmlspecialchars($task['description']); ?></div>
                                        <div class="task-status status-completed">Завершено <i class="fas fa-check"></i></div>
                                    </div>
                                    
                                    <div class="task-content">
                                        <div class="task-completed-date">
                                            <i class="fas fa-calendar-check"></i> Завершено: <?php echo date('d.m.Y H:i', strtotime($task['completed_at'])); ?>
                                        </div>
                                        
                                        <div class="task-deadline">
                                            <i class="fas fa-clock"></i> Запланировано на: <?php echo date('d.m.Y H:i', strtotime($task['event_date'] . ' ' . $task['event_time'])); ?>
                                        </div>
                                        
                                        <div class="task-description">
                                            <i class="fas fa-user"></i> Организатор: <?php echo htmlspecialchars($task['organizer']); ?>
                                        </div>
                                        
                                        <div class="task-members">
                                            <div class="member-avatar"><?php echo getInitials($task['organizer']); ?></div>
                                        </div>
                                        
                                        <div class="media-section">
                                            <h4><i class="fas fa-images"></i> Фото/видео отчеты:</h4>
                                            
                                            <?php if (!empty($task['media'])): ?>
                                                <div class="media-gallery">
                                                    <?php foreach ($task['media'] as $media): ?>
                                                        <div class="media-item">
                                                            <?php if ($media['file_type'] === 'image'): ?>
                                                                <img src="<?php echo htmlspecialchars($media['file_path']); ?>" 
                                                                     alt="Отчет о выполнении" 
                                                                     onclick="openModal('<?php echo htmlspecialchars($media['file_path']); ?>', 'image')">
                                                            <?php else: ?>
                                                                <video controls>
                                                                    <source src="<?php echo htmlspecialchars($media['file_path']); ?>" type="video/mp4">
                                                                    Ваш браузер не поддерживает видео.
                                                                </video>
                                                            <?php endif; ?>
                                                            
                                                            <?php if (!empty($media['comment'])): ?>
                                                                <div class="media-comment">
                                                                    <?php echo htmlspecialchars($media['comment']); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <div class="media-date">
                                                                <i class="fas fa-calendar"></i> <?php echo date('d.m.Y H:i', strtotime($media['uploaded_at'])); ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="no-media">
                                                    <i class="fas fa-camera"></i>
                                                    <p>Фото/видео отчеты не загружены</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="task-card">
                                <div style="text-align: center; padding: 40px; color: #666;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e0;"></i>
                                    <h3>У вас нет выполненных дел</h3>
                                    <p>Завершенные дела появятся здесь после выполнения задач</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для просмотра изображений -->
    <div id="imageModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        // Функция для открытия модального окна с изображением
        function openModal(src, type) {
            if (type === 'image') {
                const modal = document.getElementById('imageModal');
                const modalImg = document.getElementById('modalImage');
                modal.classList.add('active');
                modalImg.src = src;
            }
        }

        // Закрытие модального окна
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('imageModal').classList.remove('active');
        });

        // Закрытие по клику вне изображения
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });

        // Закрытие по ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('imageModal').classList.remove('active');
            }
        });

        // Анимация появления элементов при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            const taskCards = document.querySelectorAll('.task-card');
            taskCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>
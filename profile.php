<?php
session_start();
// Установка часового пояса (важно для правильного сравнения времени)
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

// Получаем задачи пользователя
$user_tasks = [];
$completed_tasks = [];

try {
    // Текущие задачи, где пользователь является участником ИЛИ организатором
    $stmt = $conn->prepare("
        SELECT m.*, u.first_name, u.last_name 
        FROM markers m 
        LEFT JOIN event_participants ep ON m.id = ep.event_id 
        JOIN users u ON m.user_id = u.id 
        WHERE (ep.user_id = ? OR m.user_id = ?) AND (m.status = 'active' OR m.status IS NULL)
        ORDER BY m.event_date, m.event_time ASC
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Проверяем, наступило ли время мероприятия
        // Объединяем дату и время в правильном формате
        $event_date = $row['event_date'];
        $event_time = $row['event_time'];
        
        // Создаем DateTime объекты для корректного сравнения
        $event_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $event_date . ' ' . $event_time);
        
        if (!$event_datetime) {
            // Если не удалось создать из стандартного формата, пробуем альтернативные
            $event_datetime = new DateTime($event_date . ' ' . $event_time);
        }
        
        $current_datetime = new DateTime();
        
        $row['is_event_time'] = ($current_datetime >= $event_datetime);
        $row['event_timestamp'] = $event_datetime->getTimestamp();
        $user_tasks[] = $row;
    }
    
    // Завершенные задачи
    $stmt = $conn->prepare("
        SELECT m.*, u.first_name, u.last_name 
        FROM markers m 
        LEFT JOIN event_participants ep ON m.id = ep.event_id 
        JOIN users u ON m.user_id = u.id 
        WHERE (ep.user_id = ? OR m.user_id = ?) AND m.status = 'completed'
        ORDER BY m.completed_at DESC
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $completed_tasks[] = $row;
    }
} catch (Exception $e) {
    error_log("Ошибка при получении задач: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя | TaskManager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/4.css">
    <style>
        .button-group {
    display: flex;
    gap: 10px; /* одинаковый отступ между кнопками */
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
                <a href="#" class="nav-item active">
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
                <h1>Текущие дела</h1>
                <div class="header-actions">
                    
                    <div class="user-menu-mobile">
                        <div class="user-avatar small"><?php echo getInitials($user_name); ?></div>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <?php if (count($user_tasks) > 0): ?>
                    <div class="tasks-grid">
                        <?php foreach ($user_tasks as $task): ?>
                            <div class="task-card">
                                <div class="task-card-header">
                                    <h3><?php echo htmlspecialchars($task['description']); ?></h3>
                                    <span class="task-status <?php echo $task['is_event_time'] ? 'status-active' : 'status-upcoming'; ?>">
                                        <?php echo $task['is_event_time'] ? 'В процессе' : 'Ожидается'; ?>
                                    </span>
                                </div>
                                
                                <div class="task-info">
                                    <div class="info-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('d.m.Y', $task['event_timestamp']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo date('H:i', $task['event_timestamp']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="task-card-footer">
                                    <div class="button-group">
                                        <button class="btn-icon" data-event-id="<?php echo $task['id']; ?>" title="Контакты">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        
                                        <?php if ($task['is_event_time']): ?>
                                            <button class="btn-primary" data-event-id="<?php echo $task['id']; ?>">
                                                <i class="fas fa-upload"></i> Загрузить отчет
                                            </button>
                                            <?php if ($task['user_id'] == $user_id): ?>
                                                <button class="btn-success" data-event-id="<?php echo $task['id']; ?>">
                                                    <i class="fas fa-check"></i> Завершить
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="time-info">
                                                <i class="fas fa-hourglass-start"></i>
                                                <span>Доступно после <?php echo date('d.m.Y H:i', $task['event_timestamp']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($task['is_event_time']): ?>
                                <div class="media-upload-section" id="mediaUpload-<?php echo $task['id']; ?>">
                                    <h4>Загрузить отчет</h4>
                                    <form class="upload-form" enctype="multipart/form-data" method="post">
                                        <input type="hidden" name="event_id" value="<?php echo $task['id']; ?>">
                                        <div class="form-group">
                                            <label for="media-upload-<?php echo $task['id']; ?>" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span>Выберите файлы</span>
                                            </label>
                                            <input type="file" id="media-upload-<?php echo $task['id']; ?>" name="media[]" multiple accept="image/*,video/*" class="file-input">
                                        </div>
                                        <div class="form-group">
                                            <textarea name="comment" rows="3" placeholder="Опишите, что было сделано..."></textarea>
                                        </div>
                                        <button type="button" class="btn-primary full-width" data-event-id="<?php echo $task['id']; ?>">
                                            <i class="fas fa-paper-plane"></i> Отправить отчет
                                        </button>
                                    </form>
                                    
                                    <div class="media-preview" id="mediaPreview-<?php echo $task['id']; ?>"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Нет текущих задач</h3>
                        <p>У вас нет активных задач. Создайте новую или присоединитесь к существующей.</p>
                        <a href="index.php" class="btn-primary">Посмотреть на карте</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно контактов -->
<div class="modal-overlay" id="contactModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Контакты участников</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <ul class="contacts-list" id="contactList">
                <!-- Контакты будут загружены сюда -->
            </ul>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary modal-close">Закрыть</button>
        </div>
    </div>
</div>

   <script>
document.addEventListener('DOMContentLoaded', function() {
    // Открытие/закрытие модального окна контактов
    const contactModal = document.getElementById('contactModal');
    const closeModalButtons = document.querySelectorAll('.modal-close');
    
    // Обработчики для кнопок контактов
    document.querySelectorAll('.btn-icon[title="Контакты"]').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            loadContacts(eventId);
            contactModal.classList.add('active');
        });
    });
    
    // Закрытие модального окна
    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            contactModal.classList.remove('active');
        });
    });
    
    // Закрытие по клику вне модального окна
    contactModal.addEventListener('click', (e) => {
        if (e.target === contactModal) {
            contactModal.classList.remove('active');
        }
    });
    
    // Открытие/закрытие формы загрузки медиа
    document.querySelectorAll('.btn-primary').forEach(button => {
        if (button.textContent.includes('Загрузить отчет')) {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                const mediaUpload = document.getElementById(`mediaUpload-${eventId}`);
                const isActive = mediaUpload.classList.contains('active');
                
                // Закрываем все другие открытые формы
                document.querySelectorAll('.media-upload-section.active').forEach(section => {
                    if (section.id !== `mediaUpload-${eventId}`) {
                        section.classList.remove('active');
                        const correspondingButton = document.querySelector(`.btn-primary[data-event-id="${section.id.split('-')[1]}"]`);
                        if (correspondingButton) {
                            correspondingButton.innerHTML = '<i class="fas fa-upload"></i> Загрузить отчет';
                        }
                    }
                });
                
                mediaUpload.classList.toggle('active');
                
                if (!isActive) {
                    this.innerHTML = '<i class="fas fa-times"></i> Скрыть форму';
                } else {
                    this.innerHTML = '<i class="fas fa-upload"></i> Загрузить отчет';
                }
            });
        }
    });
    
    // Обработка завершения дела
    document.querySelectorAll('.btn-success').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            if (confirm('Вы уверены, что хотите завершить это дело? После завершения вы больше не сможете добавлять материалы.')) {
                completeEvent(eventId);
            }
        });
    });
    
    // Предпросмотр выбранных медиафайлов
    document.querySelectorAll('.file-input').forEach(input => {
        input.addEventListener('change', function() {
            const eventId = this.id.split('-').pop();
            const previewContainer = document.getElementById(`mediaPreview-${eventId}`);
            previewContainer.innerHTML = '';
            
            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    const mediaItem = document.createElement('div');
                    mediaItem.classList.add('media-item');
                    
                    reader.onload = function(e) {
                        if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = 'Предпросмотр';
                            mediaItem.appendChild(img);
                        } else if (file.type.startsWith('video/')) {
                            const video = document.createElement('video');
                            video.src = e.target.result;
                            video.controls = true;
                            mediaItem.appendChild(video);
                        }
                        previewContainer.appendChild(mediaItem);
                    };
                    
                    reader.readAsDataURL(file);
                });
            }
        });
    });
    
    // Отправка медиафайлов
    document.querySelectorAll('.btn-primary.full-width').forEach(button => {
        if (button.textContent.includes('Отправить отчет')) {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                uploadMedia(eventId);
            });
        }
    });
});

// Функция загрузки контактов
function loadContacts(eventId) {
    const contactList = document.getElementById('contactList');
    contactList.innerHTML = '<li class="no-contacts">Загрузка контактов...</li>';
    
    fetch('processes/get_contacts.php?event_id=' + eventId)
        .then(response => response.json())
        .then(data => {
            contactList.innerHTML = '';
            
            if (data.success && data.contacts.length > 0) {
                data.contacts.forEach(contact => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <div class="member-avatar">${getInitials(contact.name)}</div>
                        <div>
                            <div class="contact-name">${contact.name}</div>
                            <div class="contact-phone">Телефон: ${contact.phone || 'Не указан'}</div>
                            <div class="contact-email">Email: ${contact.email}</div>
                        </div>
                    `;
                    contactList.appendChild(li);
                });
            } else {
                contactList.innerHTML = '<li class="no-contacts">Контакты не найдены</li>';
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки контактов:', error);
            contactList.innerHTML = '<li class="no-contacts">Ошибка загрузки контактов</li>';
        });
}

// Функция загрузки медиафайлов
function uploadMedia(eventId) {
    const filesInput = document.getElementById(`media-upload-${eventId}`);
    const commentInput = document.querySelector(`#mediaUpload-${eventId} textarea[name="comment"]`);
    const submitButton = document.querySelector(`.btn-primary.full-width[data-event-id="${eventId}"]`);
    
    if (!filesInput.files.length) {
        alert('Пожалуйста, выберите файлы для загрузки');
        return;
    }
    
    const formData = new FormData();
    for (let i = 0; i < filesInput.files.length; i++) {
        formData.append('media[]', filesInput.files[i]);
    }
    formData.append('event_id', eventId);
    formData.append('comment', commentInput.value);
    
    // Показываем индикатор загрузки
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
    submitButton.disabled = true;
    
    fetch('processes/upload_media.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Отчет успешно загружен!');
            // Закрываем форму и очищаем её
            const mediaUpload = document.getElementById(`mediaUpload-${eventId}`);
            mediaUpload.classList.remove('active');
            filesInput.value = '';
            commentInput.value = '';
            document.getElementById(`mediaPreview-${eventId}`).innerHTML = '';
            
            // Возвращаем текст кнопки "Загрузить отчет"
            const uploadButton = document.querySelector(`.btn-primary[data-event-id="${eventId}"]:not(.full-width)`);
            if (uploadButton) {
                uploadButton.innerHTML = '<i class="fas fa-upload"></i> Загрузить отчет';
            }
        } else {
            alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Ошибка загрузки файлов: ' + error.message);
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// Функция завершения дела
function completeEvent(eventId) {
    if (!confirm('Вы уверены, что хотите завершить это дело? Это действие нельзя отменить.')) {
        return;
    }
    
    fetch('processes/complete_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ event_id: eventId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Дело успешно завершено!');
            location.reload();
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка завершения дела: ' + error.message);
    });
}

// Функция для получения инициалов
function getInitials(name) {
    return name.split(' ').map(n => n.charAt(0).toUpperCase()).join('');
}
</script>
</body>
</html>
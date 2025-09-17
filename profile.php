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
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="css/3.css">
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
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="user-info">
                    <div class="user-info-avatar"><?php echo getInitials($user_name); ?></div>
                    <div class="user-info-name"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="user-info-email"><?php echo htmlspecialchars($user_email); ?></div>
                </div>
                
                <ul class="nav-menu">
                    <li><a href="#" class="active" id="currentTasksLink">Текущие дела</a></li>
                    <li><a href="completed_tasks.php" id="completedTasksLink">Выполненные дела</a></li>
                    <li><a href="processes/logout.php" style="color: #ff4444;">Выйти</a></li>
                </ul>
            </div>
            
            <div class="profile-main">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Текущие дела</div>
                    </div>
                    
                    <div class="tasks-container" id="tasksContainer">
                        <?php if (count($user_tasks) > 0): ?>
                            <?php foreach ($user_tasks as $task): ?>
                                <div class="task-card">
                                    <div class="task-header">
                                        <div class="task-title"><?php echo htmlspecialchars($task['description']); ?></div>
                                        <div class="task-status <?php echo $task['is_event_time'] ? 'status-active' : 'status-upcoming'; ?>">
                                            <?php echo $task['is_event_time'] ? 'В процессе' : 'Ожидается'; ?>
                                        </div>
                                    </div>
                                    <div class="task-deadline">
                                        <?php echo $task['is_event_time'] ? 'Началось: ' : 'Начнется: '; ?>
                                        <?php echo date('d.m.Y H:i', $task['event_timestamp']); ?>
                                    </div>
                                    <div class="task-description">Организатор: <?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></div>
                                    
                                    <div class="task-members">
                                        <div class="member-avatar"><?php echo getInitials($task['first_name'] . ' ' . $task['last_name']); ?></div>
                                    </div>
                                    
                                    <div class="task-actions">
                                        <button class="btn btn-primary btn-contact" data-event-id="<?php echo $task['id']; ?>">Контакты</button>
                                        
                                        <?php if ($task['is_event_time']): ?>
                                            <button class="btn btn-outline btn-upload" data-event-id="<?php echo $task['id']; ?>">Загрузить отчет</button>
                                            <?php if ($task['user_id'] == $user_id): ?>
                                                <button class="btn btn-success btn-complete" data-event-id="<?php echo $task['id']; ?>">Завершить дело</button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn btn-outline" disabled>Доступно после <?php echo date('d.m.Y H:i', $task['event_timestamp']); ?></button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($task['is_event_time']): ?>
                                    <div class="media-upload" id="mediaUpload-<?php echo $task['id']; ?>">
                                        <h4>Загрузить фото/видео отчета</h4>
                                        <form class="upload-form" enctype="multipart/form-data" method="post">
                                            <input type="hidden" name="event_id" value="<?php echo $task['id']; ?>">
                                            <div class="form-group">
                                                <label for="media-upload-<?php echo $task['id']; ?>">Выберите файлы</label>
                                                <input type="file" id="media-upload-<?php echo $task['id']; ?>" name="media[]" multiple accept="image/*,video/*">
                                            </div>
                                            <div class="form-group">
                                                <label for="media-comment-<?php echo $task['id']; ?>">Описание выполненной работы</label>
                                                <textarea id="media-comment-<?php echo $task['id']; ?>" name="comment" rows="3" placeholder="Опишите, что было сделано..."></textarea>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-submit-media" data-event-id="<?php echo $task['id']; ?>">Отправить отчет</button>
                                        </form>
                                        
                                        <div class="media-preview" id="mediaPreview-<?php echo $task['id']; ?>"></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>У вас нет текущих задач.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="contact-modal" id="contactModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Контакты участников</div>
                <button class="close-modal">&times;</button>
            </div>
            
            <ul class="contact-list" id="contactList">
                <!-- Контакты будут загружены через AJAX -->
            </ul>
            
            <button class="btn btn-primary close-modal">Закрыть</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Открытие/закрытие модального окна контактов
            const contactButtons = document.querySelectorAll('.btn-contact');
            const contactModal = document.getElementById('contactModal');
            const closeModalButtons = document.querySelectorAll('.close-modal');
            
            contactButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const eventId = button.getAttribute('data-event-id');
                    loadContacts(eventId);
                    contactModal.classList.add('active');
                });
            });
            
            closeModalButtons.forEach(button => {
                button.addEventListener('click', () => {
                    contactModal.classList.remove('active');
                });
            });
            
            // Открытие/закрытие формы загрузки медиа
            const uploadButtons = document.querySelectorAll('.btn-upload');
            
            uploadButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const eventId = button.getAttribute('data-event-id');
                    const mediaUpload = document.getElementById(`mediaUpload-${eventId}`);
                    mediaUpload.classList.toggle('active');
                    
                    if (mediaUpload.classList.contains('active')) {
                        button.textContent = 'Скрыть форму';
                    } else {
                        button.textContent = 'Загрузить отчет';
                    }
                });
            });
            
            // Обработка завершения дела
            const completeButtons = document.querySelectorAll('.btn-complete');
            
            completeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.getAttribute('data-event-id');
                    if (confirm('Вы уверены, что хотите завершить это дело? После завершения вы больше не сможете добавлять материалы.')) {
                        completeEvent(eventId);
                    }
                });
            });
            
            // Предпросмотр выбранных медиафайлов
            const mediaInputs = document.querySelectorAll('input[type="file"]');
            
            mediaInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const eventId = this.id.split('-').pop();
                    const previewContainer = document.getElementById(`mediaPreview-${eventId}`);
                    previewContainer.innerHTML = '';
                    
                    if (this.files) {
                        Array.from(this.files).forEach(file => {
                            const reader = new FileReader();
                            const mediaItem = document.createElement('div');
                            mediaItem.classList.add('media-item');
                            
                            reader.onload = function(e) {
                                if (file.type.startsWith('image/')) {
                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    mediaItem.appendChild(img);
                                } else if (file.type.startsWith('video/')) {
                                    const video = document.createElement('video');
                                    video.src = e.target.result;
                                    video.controls = true;
                                    mediaItem.appendChild(video);
                                }
                                
                                previewContainer.appendChild(mediaItem);
                            }
                            
                            reader.readAsDataURL(file);
                        });
                    }
                });
            });
            
            // Отправка медиафайлов
            const submitButtons = document.querySelectorAll('.btn-submit-media');
            
            submitButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.getAttribute('data-event-id');
                    uploadMedia(eventId);
                });
            });
        });
        
        // Функция загрузки контактов
        function loadContacts(eventId) {
            fetch('processes/get_contacts.php?event_id=' + eventId)
                .then(response => response.json())
                .then(data => {
                    const contactList = document.getElementById('contactList');
                    contactList.innerHTML = '';
                    
                    if (data.success && data.contacts.length > 0) {
                        data.contacts.forEach(contact => {
                            const li = document.createElement('li');
                            li.innerHTML = `
                                <div class="member-avatar">${getInitials(contact.name)}</div>
                                <div>
                                    <div>${contact.name}</div>
                                    <div>Телефон: ${contact.phone || 'Не указан'}</div>
                                    <div>Email: ${contact.email}</div>
                                </div>
                            `;
                            contactList.appendChild(li);
                        });
                    } else {
                        contactList.innerHTML = '<li>Контакты не найдены</li>';
                    }
                })
                .catch(error => {
                    console.error('Ошибка загрузки контактов:', error);
                });
        }
        
        // Функция загрузки медиафайлов
        // Функция загрузки медиафайлов
function uploadMedia(eventId) {
    console.log("Uploading media for event:", eventId);
    
    const formData = new FormData();
    const filesInput = document.getElementById(`media-upload-${eventId}`);
    const commentInput = document.getElementById(`media-comment-${eventId}`);
    
    console.log("Files selected:", filesInput.files.length);
    
    for (let i = 0; i < filesInput.files.length; i++) {
        formData.append('media[]', filesInput.files[i]);
        console.log("Added file to FormData:", filesInput.files[i].name);
    }
    
    formData.append('event_id', eventId);
    formData.append('comment', commentInput.value);
    
    console.log("Sending FormData...");
    
    // Показываем индикатор загрузки
    const submitButton = document.querySelector(`.btn-submit-media[data-event-id="${eventId}"]`);
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Загрузка...';
    submitButton.disabled = true;
    
    fetch('processes/upload_media.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log("Response status:", response.status);
        return response.json();
    })
    .then(data => {
        console.log("Full response data:", data);
        
        // Восстанавливаем кнопку
        submitButton.textContent = originalText;
        submitButton.disabled = false;
        
        if (data.success) {
            alert('Отчет успешно загружен!');
            document.getElementById(`mediaUpload-${eventId}`).classList.remove('active');
            document.querySelector(`.btn-upload[data-event-id="${eventId}"]`).textContent = 'Загрузить отчет';
            
            // Очищаем форму
            filesInput.value = '';
            commentInput.value = '';
            document.getElementById(`mediaPreview-${eventId}`).innerHTML = '';
            
            // Показываем информацию о загруженных файлах
            if (data.files && data.files.length > 0) {
                console.log("Uploaded files:", data.files);
            }
            if (data.errors && data.errors.length > 0) {
                console.warn("Upload errors:", data.errors);
            }
            
        } else {
            // Более подробное сообщение об ошибке
            let errorMessage = 'Ошибка: ' + data.message;
            if (data.errors && data.errors.length > 0) {
                errorMessage += '\n' + data.errors.join('\n');
            }
            alert(errorMessage);
            console.error('Server error details:', data);
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        
        // Восстанавливаем кнопку
        submitButton.textContent = originalText;
        submitButton.disabled = false;
        
        alert('Ошибка загрузки файлов: ' + error.message);
    });
}
        
        // Функция завершения дела
        function completeEvent(eventId) {
            fetch('processes/complete_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ event_id: eventId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сети');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Дело успешно завершено!');
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Ошибка завершения дела: ' + error.message);
            });
        }
        
        // Функция для получения инициалов из строки
        function getInitials(name) {
            return name.split(' ').map(n => n.charAt(0).toUpperCase()).join('');
        }
    </script>
</body>
</html>
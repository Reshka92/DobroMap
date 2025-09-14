<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="css/3.css">
    <style>
        
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">TaskManager</div>
            <div class="user-menu">
                <div class="user-avatar">U</div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="user-info">
                    <div class="user-info-avatar">U</div>
                    <div class="user-info-name">Иван Иванов</div>
                    <div class="user-info-email">ivan@example.com</div>
                </div>
                
                <ul class="nav-menu">
                    <li><a href="#" class="active">Текущие дела</a></li>
                    <li><a href="#">Выполненные дела</a></li>
                    <li><a href="#">Настройки профиля</a></li>
                    <li><a href="logout.php">Выйти</a></li>
                </ul>
            </div>
            
            <div class="profile-main">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Текущие дела</div>
                    </div>
                    
                    <div class="tasks-container">
                        <div class="task-card">
                            <div class="task-header">
                                <div class="task-title">Ремонт в офисе</div>
                                <div class="task-status status-active">В процессе</div>
                            </div>
                            <div class="task-deadline">До: 25.10.2023</div>
                            <div class="task-description">Необходимо выполнить косметический ремонт в центральном офисе компании.</div>
                            
                            <div class="task-members">
                                <div class="member-avatar">ИИ</div>
                                <div class="member-avatar">АС</div>
                                <div class="member-avatar">ПК</div>
                                <div class="more-members">+3</div>
                            </div>
                            
                            <div class="task-actions">
                                <button class="btn btn-primary btn-contact">Контакты</button>
                                <button class="btn btn-outline btn-upload">Загрузить отчет</button>
                            </div>
                            
                            <div class="media-upload">
                                <h4>Загрузить фото/видео отчета</h4>
                                <form class="upload-form">
                                    <div class="form-group">
                                        <label for="media-upload">Выберите файлы</label>
                                        <input type="file" id="media-upload" multiple accept="image/*,video/*">
                                    </div>
                                    <div class="form-group">
                                        <label for="media-comment">Комментарий</label>
                                        <textarea id="media-comment" rows="3" placeholder="Опишите выполненную работу..."></textarea>
                                    </div>
                                    <button type="button" class="btn btn-primary">Отправить</button>
                                </form>
                                
                                <div class="media-preview">
                                    <div class="media-item">
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150' viewBox='0 0 150 150'%3E%3Crect width='150' height='150' fill='%23ddd'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='monospace' font-size='14' fill='%23999'%3EПревью%3C/text%3E%3C/svg%3E" alt="Превью">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="task-card">
                            <div class="task-header">
                                <div class="task-title">Организация мероприятия</div>
                                <div class="task-status status-active">В процессе</div>
                            </div>
                            <div class="task-deadline">До: 30.10.2023</div>
                            <div class="task-description">Подготовка корпоративного мероприятия для сотрудников компании.</div>
                            
                            <div class="task-members">
                                <div class="member-avatar">ИИ</div>
                                <div class="member-avatar">МП</div>
                                <div class="more-members">+5</div>
                            </div>
                            
                            <div class="task-actions">
                                <button class="btn btn-primary btn-contact">Контакты</button>
                                <button class="btn btn-outline btn-upload">Загрузить отчет</button>
                            </div>
                            
                            <div class="media-upload">
                                <h4>Загрузить фото/видео отчета</h4>
                                <form class="upload-form">
                                    <div class="form-group">
                                        <label for="media-upload">Выберите файлы</label>
                                        <input type="file" id="media-upload" multiple accept="image/*,video/*">
                                    </div>
                                    <div class="form-group">
                                        <label for="media-comment">Комментарий</label>
                                        <textarea id="media-comment" rows="3" placeholder="Опишите выполненную работу..."></textarea>
                                    </div>
                                    <button type="button" class="btn btn-primary">Отправить</button>
                                </form>
                                
                                <div class="media-preview">
                                    <div class="media-item">
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150' viewBox='0 0 150 150'%3E%3Crect width='150' height='150' fill='%23ddd'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='monospace' font-size='14' fill='%23999'%3EПревью%3C/text%3E%3C/svg%3E" alt="Превью">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="task-card">
                            <div class="task-header">
                                <div class="task-title">Обновление IT-инфраструктуры</div>
                                <div class="task-status status-completed">Завершено</div>
                            </div>
                            <div class="task-deadline">Завершено: 15.10.2023</div>
                            <div class="task-description">Модернизация серверного оборудования и рабочих станций.</div>
                            
                            <div class="task-members">
                                <div class="member-avatar">ИИ</div>
                                <div class="member-avatar">ДС</div>
                                <div class="member-avatar">АБ</div>
                            </div>
                            
                            <div class="task-actions">
                                <button class="btn btn-primary btn-contact">Контакты</button>
                                <button class="btn btn-outline" disabled>Загрузить отчет</button>
                            </div>
                        </div>
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
            
            <ul class="contact-list">
                <li>
                    <div class="member-avatar">ИИ</div>
                    <div>
                        <div>Иван Иванов</div>
                        <div>Телефон: +7 (123) 456-78-90</div>
                        <div>Email: ivan@example.com</div>
                    </div>
                </li>
                <li>
                    <div class="member-avatar">АС</div>
                    <div>
                        <div>Анна Смирнова</div>
                        <div>Телефон: +7 (234) 567-89-01</div>
                        <div>Email: anna@example.com</div>
                    </div>
                </li>
                <li>
                    <div class="member-avatar">ПК</div>
                    <div>
                        <div>Петр Кузнецов</div>
                        <div>Телефон: +7 (345) 678-90-12</div>
                        <div>Email: petr@example.com</div>
                    </div>
                </li>
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
                    const mediaUpload = button.closest('.task-card').querySelector('.media-upload');
                    mediaUpload.classList.toggle('active');
                    
                    if (mediaUpload.classList.contains('active')) {
                        button.textContent = 'Закрыть';
                    } else {
                        button.textContent = 'Загрузить отчет';
                    }
                });
            });
            
            // Предпросмотр выбранных медиафайлов
            const mediaInputs = document.querySelectorAll('input[type="file"]');
            
            mediaInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const previewContainer = this.closest('.media-upload').querySelector('.media-preview');
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
        });
    </script>
</body>
</html>
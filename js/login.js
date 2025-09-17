const form = document.getElementById('loginForm');

// Добавляем checkbox "Запомнить меня" в форму
form.innerHTML += `
    <div class="input-group" style="display: flex; align-items: center; margin: 10px 0;">
        <input type="checkbox" id="remember" style="margin-right: 10px;">
        <label for="remember">Запомнить меня</label>
    </div>
`;

form.addEventListener("submit", async function(event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const remember = document.getElementById('remember').checked;

    if (!email || !password) {
        alert("Пожалуйста, заполните все поля.");
        return;
    }

    try {
        const response = await fetch('../processes/login.php', {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, password, remember })
        });

        const result = await response.json();

        if (result.success) {
            // Сохраняем информацию о входе
            localStorage.setItem('loggedIn', 'true');
            localStorage.setItem('userEmail', email);
            
            // Показываем уведомление об успешном входе
            showNotification('Вход выполнен успешно!', 'success');
            
            // Перенаправляем после небольшой задержки
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1000);
            
        } else {
            showNotification(result.message || "Неверный email или пароль.", 'error');
        }
    } catch (error) {
        console.error("Ошибка входа:", error);
        showNotification("Ошибка соединения с сервером.", 'error');
    }
});

// Функция для показа уведомлений
function showNotification(message, type) {
    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '10px 20px';
    notification.style.borderRadius = '5px';
    notification.style.color = 'white';
    notification.style.zIndex = '10000';
    
    if (type === 'success') {
        notification.style.background = '#4CAF50';
    } else {
        notification.style.background = '#F44336';
    }
    
    // Добавляем уведомление на страницу
    document.body.appendChild(notification);
    
    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
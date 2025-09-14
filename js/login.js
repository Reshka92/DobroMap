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

        const text = await response.text();

        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error("Сервер вернул не JSON:", text);
            alert("Ошибка соединения с сервером.");
            return;
        }

        if (result.success) {
            localStorage.setItem('loggedIn', 'true');
            localStorage.setItem('userEmail', email);
            window.location.href = result.redirect;
            console.log("Успешный вход");
            
        } else {
            alert(result.message || "Неверный email или пароль.");
        }
    } catch (error) {
        console.error("Ошибка входа:", error);
        alert("Ошибка соединения с сервером.");
    }
});
const form = document.getElementById('loginForm');

form.addEventListener("submit", async function(event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    if (!email || !password) {
        alert("Пожалуйста, заполните все поля.");
        return;
    }

    try {
        const response = await fetch('../processes/login.php', {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, password })
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
            console.log("Успешная регистрация");
            
        } else {
            alert(result.message || "Неверный email или пароль.");
        }
    } catch (error) {
        console.error("Ошибка входа:", error);
        alert("Ошибка соединения с сервером.");
    }
});

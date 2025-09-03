<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="css/2.css">
</head>
<body>
    <div class="container">
    <div class="container" >
        <div class="logo">
            <h1>Регистрация</h1>
        </div>
        
        <form id="registerForm">
            <div class="input-group">
                <input type="text" placeholder="Имя" id="name" required>
            </div>
            
            <div class="input-group">
                <input type="text" placeholder="Фамилия" id="lastname" required>
            </div>
            
            <div class="input-group">
                <input type="number" placeholder="Возраст" id="age" min="1" required>
            </div>
            
            <div class="input-group">
                <input type="tel" placeholder="Телефон" id="number" required>
            </div>
            
            <div class="input-group">
                <input type="" placeholder="Email" id="email" required>
            </div>
            
            <div class="input-group">
                <input type="password" placeholder="Пароль" id="password" required>
            </div>
            
            <div class="input-group">
                <input type="repeatPassword" id="repeatPassword" placeholder="Повторите пароль" id="password" required>
            </div>
            
            <button type="submit" class="btn-login">Зарегистрироваться</button>
            
            <div class="forgot-password">
                <a href="login.php">Уже есть аккаунт? Войти</a>
            </div>
        </form>
    </div>


<script src="js/register.js"></script>
</body>
</html>
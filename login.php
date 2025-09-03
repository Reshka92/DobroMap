<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="css/2.css">
</head>
<body>
    <div class="container">
    <div class="container" >
        <div class="logo">
            <h1>Вход</h1>
        </div>
        
        <form id="loginForm">
            
            <div class="input-group">
                <input type="email" placeholder="Email" id="email" required>
            </div>
            
            <div class="input-group">
                <input type="password" placeholder="Пароль" id="password" required>
            </div>
            
            
            <button type="submit" class="btn-login">Войти в аккаунт</button>
            
            <div class="forgot-password">
                <a href="register.php">Еще не зарегистрировались?</a>
            </div>
        </form>
    </div>


<script src="js/login.js"></script>
</body>
</html>
<?php
session_start();

// Подключаем конфигурацию и базу данных
require_once 'includes/config.php';
require_once 'includes/db.php';

// Проверяем авторизацию через сессию и куки (если есть remember_token)
$isLoggedIn = false;
$userId = 0;
$userName = '';

if (isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn']) {
    // Пользователь авторизован через сессию
    $isLoggedIn = true;
    $userId = $_SESSION['user_id'] ?? 0;
    $userName = $_SESSION['user_name'] ?? '';
} elseif (isset($_COOKIE['remember_token'])) {
    // Пытаемся авторизовать через remember token
    $token = $_COOKIE['remember_token'];
    
    try {
        $stmt = $conn->prepare("SELECT user_id, expires_at FROM user_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $tokenData = $result->fetch_assoc();
            
            // Проверяем, не истек ли токен
            if ($tokenData['expires_at'] > time()) {
                // Получаем данные пользователя
                $userStmt = $conn->prepare("SELECT id, email, first_name, last_name FROM users WHERE id = ?");
                $userStmt->bind_param("i", $tokenData['user_id']);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                
                if ($userResult->num_rows > 0) {
                    $user = $userResult->fetch_assoc();
                    
                    // Устанавливаем сессию
                    $_SESSION['isLoggedIn'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    
                    $isLoggedIn = true;
                    $userId = $user['id'];
                    $userName = $_SESSION['user_name'];
                    
                    // Обновляем время жизни токена
                    $newExpires = time() + (30 * 24 * 60 * 60);
                    $updateStmt = $conn->prepare("UPDATE user_tokens SET expires_at = ? WHERE token = ?");
                    $updateStmt->bind_param("is", $newExpires, $token);
                    $updateStmt->execute();
                    
                    // Обновляем cookie
                    setcookie('remember_token', $token, $newExpires, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
                }
            } else {
                // Удаляем просроченный токен
                $deleteStmt = $conn->prepare("DELETE FROM user_tokens WHERE token = ?");
                $deleteStmt->bind_param("s", $token);
                $deleteStmt->execute();
                
                // Удаляем cookie
                setcookie('remember_token', '', time() - 3600, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
            }
        }
    } catch (Exception $e) {
        error_log("Ошибка при проверке remember token: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>DobroMap - Карта добрых дел</title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="css/1.css"/>
  </head>
  <body>
    <!-- Карта -->
    <div id="map" class="map"></div>
    
    <!-- Контейнер с элементами управления -->
    <div class="controls-container" id="controls-container">
        <?php if (!$isLoggedIn): ?>
            <button class="btnLogIn" id="btnLogIn"><a id="aBtn" href="register.php" style="color:white">Регистрация</a></button>
        <?php else: ?>
            <a href="profile.php"><img src="images/User2.png" style="height: 40px;width: 40px;" id="userImg" class="SearchImg" alt="Профиль"></a>
            <a href="index.php"><img class="SearchImg" src="images/Dela.png" id="DelaImg" style="height: 30px;width: 30px;" alt="Дела"></a>
        <?php endif; ?>
        <a href="index.php"><img src="images/Search.png" class="SearchImg" alt="Поиск"></a>
        <a href="index.php"><img src="images/Graphic.png" class="SearchImg" alt="График"></a>
        <button id="addMarkerBtn" class="btnLogIn">Добавить метку</button>
    </div>
    
    <script>
       window.userStatus = {
        isLoggedIn: <?php echo $isLoggedIn ? 'true' : 'false'; ?>,
        userId: <?php echo $userId; ?>,
        userName: '<?php echo addslashes($userName); ?>'
       };
       
       // Функция для проверки авторизации при загрузке
       function checkAuthStatus() {
           if (window.userStatus.isLoggedIn) {
               // Пользователь авторизован
               document.getElementById('btnLogIn').style.display = 'none';
               document.getElementById('userImg').style.display = 'block';
               document.getElementById('DelaImg').style.display = 'block';
           } else {
               // Пользователь не авторизован
               document.getElementById('btnLogIn').style.display = 'block';
               document.getElementById('userImg').style.display = 'none';
               document.getElementById('DelaImg').style.display = 'none';
           }
       }
       
       // Проверяем статус при загрузке
       document.addEventListener('DOMContentLoaded', checkAuthStatus);
    </script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=f84ab56f-6f82-4601-a010-1b6d1d69d29e&lang=ru_RU"></script>
    <script src="js/map.js"></script>
    <script src="js/markerManager.js"></script>
    <script src="js/switchInter.js"></script>
  </body>
</html>
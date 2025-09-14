<?php
//подключение к бд
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'tpghpop123');
define('DB_NAME', 'dobro_map');

// Увеличиваем время жизни сессии до 30 дней
define('SESSION_TIMEOUT', 30 * 24 * 60 * 60); // 30 дней в секундах
define('SESSION_NAME', 'DOBROMAP_SESSION'); // Уникальное имя сессии

// Настройки cookies
define('COOKIE_LIFETIME', 30 * 24 * 60 * 60); // 30 дней
define('COOKIE_PATH', '/');
define('COOKIE_DOMAIN', ''); // Ваш домен
define('COOKIE_SECURE', false);
define('COOKIE_HTTPONLY', true);
?>
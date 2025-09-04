<?php
session_start();
require_once '../includes/db.php'; // подключаем соединение с БД

header('Content-Type: application/json');

try {
    // Получаем JSON-данные
    $json = file_get_contents('php://input');
    if ($json === false) {
        throw new Exception('Ошибка чтения данных');
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Неверный формат JSON');
    }

    // Проверка обязательных полей
    $required = ['first_name', 'last_name', 'age', 'phone', 'adult', 'email', 'password'];
    foreach ($required as $field) {
        if (!array_key_exists($field, $data)) {
    throw new Exception("Поле '$field' обязательно");
}

    }

    // Проверка на существующий email
    $email = mysqli_real_escape_string($conn, $data['email']);
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        throw new Exception("Пользователь с таким email уже существует");
    }

    // Хешируем пароль
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

    // Подготовка данных
    $first_name = mysqli_real_escape_string($conn, $data['first_name']);
    $last_name = mysqli_real_escape_string($conn, $data['last_name']);
    $age = (int)$data['age'];
    $phone = mysqli_real_escape_string($conn, $data['phone']);
    $adult = (int)$data['adult'];

    // SQL-запрос
    $sql = "INSERT INTO users (first_name, last_name, age, phone, adult, email, password_hash)
            VALUES ('$first_name', '$last_name', $age, '$phone', $adult, '$email', '$password_hash')";

    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Ошибка при сохранении: " . mysqli_error($conn));
    }
    $_SESSION['isLoggedIn'] = true;
    // Успешный ответ
    echo json_encode([
        "success" => true,
        "redirect" => "http://martynov.192.ru/index.php",
        "isLoggedIn" => true
        
    ]);
    exit;
    $isLoggedIn = !empty($_SESSION['isLoggedIn']) ? 'true' : 'false';
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}

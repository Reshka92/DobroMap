<?php
require_once 'config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(!$conn){
    die("Ошибка подключения:" . mysqli_connect_error());
}
if(session_status() === PHP_SESSION_NONE){
    session_set_cookie_params(SESSION_TIMEOUT);
    session_start();
}

<?php 
  session_start();
  $isLoggedIn = !empty($_SESSION['isLoggedIn']) ? 'true' : 'false';
  $userId = $_SESSION['user_id'] ?? 0;
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
        <button class="btnLogIn" id="btnLogIn"><a id="aBtn" href="register.php" style="color:white">Регистрация</a></button>
        <a href="profile.php"><img src="images/User2.png" style="height: 40px;width: 40px;" id="userImg" class="SearchImg" alt=""></a>
        <a href="index.php"><img class="SearchImg" src="images/Dela.png" id="DelaImg" style="height: 30px;width: 30px;" alt=""></a>
        <a href="index.php"><img src="images/Search.png" class="SearchImg" alt="Дела"></a>
        <a href="index.php"><img src="images/Graphic.png" class="SearchImg" alt="График"></a>
        <button id="addMarkerBtn" class="btnLogIn">Добавить метку</button>
    </div>
    
    <script>
       window.userStatus = {
        isLoggedIn: <?php echo $isLoggedIn; ?>,
        userId: <?php echo $userId; ?>
       };
    </script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=f84ab56f-6f82-4601-a010-1b6d1d69d29e&lang=ru_RU"></script>
    <script src="js/map.js"></script>
    <script src="js/markerManager.js"></script>
    <script src="js/switchInter.js"></script>
  </body>
</html>
[file content end]
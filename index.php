<?php 
  session_start();
  $isLoggedIn = $isLoggedIn = !empty($_SESSION['isLoggedIn']) ? 'true' : 'false';
?>
<!DOCTYPE html>
<html>
  <head>
    <title>DobroMap - Карта добрых дел</title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="css/1.css"/>
    <style>
      
    </style>
  </head>
  <body>
    <!-- Карта -->
    <div id="map" class="map"></div>
    
    <!-- Контейнер с элементами управления -->
    <div class="controls-container" id="controls-container">
        <button class="btnLogIn" id="btnLogIn"><a id="aBtn" href="register.php" style="color:white" >Регистрация</a></button>
        <img src="images/User2.png" style="height: 40px;width: 40px;" id="userImg" class="SearchImg" alt="Поиск">
        <img src="images/Search.png" class="SearchImg" alt="Поиск">
        <img src="images/Graphic.png" class="SearchImg" alt="График">
    </div>
    
    <script>
       window.userStatus = {
        isLoggedIn: <?php echo $isLoggedIn; ?>
    };
    </script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=f84ab56f-6f82-4601-a010-1b6d1d69d29e&lang=ru_RU"></script>
    <script src="js/map.js"></script>
    <script src="js/switchInter.js"></script>
  </body>
</html>
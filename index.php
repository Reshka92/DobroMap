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
    <div class="controls-container">
        <button class="btnLogIn"><a href="register.php" style="color:white">Регистрация</a></button>
        <img src="images/Search.png" class="SearchImg" alt="Поиск">
        <img src="images/Graphic.png" class="SearchImg" alt="График">
    </div>
    

    <script src="https://api-maps.yandex.ru/2.1/?apikey=f84ab56f-6f82-4601-a010-1b6d1d69d29e&lang=ru_RU"></script>
    <script src="js/map.js"></script>
  </body>
</html>
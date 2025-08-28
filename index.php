<!DOCTYPE html>
<html>
  <head>
    <title>Полноэкранная карта</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="css/style.css">
    <style>
      
    </style>
  </head>
  <body>
    <div id="map" class="map">
        <div class="header">
            <header>
                <img src="images/DAA.png" id="logo"class="logoImg" alt="">
                <span></span>
            </header>
        </div>
    </div>
  <script src="https://api-maps.yandex.ru/2.1/?apikey=f84ab56f-6f82-4601-a010-1b6d1d69d29e&lang=ru_RU"></script>
  <script src="js/map.js"></script>
  <script>
    ymaps.ready(initMap);
  </script>
  </body>
</html>

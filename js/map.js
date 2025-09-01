 let map;

        // Функция инициализации карты
        function initMap() {
            const center = [44.61665, 33.52536]; 

            map = new ymaps.Map("map", {
                center: center,
                zoom: 12
            });
            
            // Удаляем все стандартные элементы управления
            map.controls.remove('trafficControl');
            map.controls.remove('fullscreenControl');
            map.controls.remove('zoomControl');
            map.controls.remove('rulerControl');
            map.controls.remove('typeSelector');
            map.controls.remove('searchControl');
            map.controls.remove('geolocationControl');
            
            // Добавляем нужные элементы управления с кастомным позиционированием
            // Кнопка слоев (вид со спутника) - левый нижний угол
            map.controls.add('typeSelector', {
                position: {
                    top: 'auto',
                    bottom: '140px', // Ниже кнопки "Найти место"
                    left: '10px',
                    right: 'auto'
                }
            });
            
            // Масштаб - правый нижний угол (прижимаем)
            map.controls.add('zoomControl', {
                position: {
                    top: 'auto',
                    bottom: '40px',
                    left: 'auto',
                    right: '10px'
                }
            });

            // Скрываем копирайты Яндекса после загрузки карты
            map.events.add('load', function() {
                // Ждем немного, чтобы элементы успели отрендериться
                setTimeout(hideYandexElements, 1000);
            });
        }

        // Функция для скрытия элементов Яндекса
        function hideYandexElements() {
            // Скрываем копирайты
            const copyrights = document.querySelectorAll('.ymaps-2-1-79-copyrights-pane, .ymaps-2-1-79-map-copyrights-promo, .ymaps-2-1-79-copyright__wrap');
            copyrights.forEach(el => {
                el.style.display = 'none';
            });
            
            // Скрываем кнопки "Открыть в Яндекс Картах" и "Создать свою карту"
            const yandexButtons = document.querySelectorAll('.ymaps-2-1-79-open-button, .ymaps-2-1-79-create-route-button');
            yandexButtons.forEach(el => {
                el.style.display = 'none';
            });
            
            // Скрываем кнопку полноэкранного режима
            const fullscreenButtons = document.querySelectorAll('.ymaps-2-1-79-fullscreen-button');
            fullscreenButtons.forEach(el => {
                el.style.display = 'none';
            });
            
            // Периодически проверяем, не появились ли элементы снова
            setInterval(() => {
                copyrights.forEach(el => {
                    if (el.style.display !== 'none') el.style.display = 'none';
                });
                yandexButtons.forEach(el => {
                    if (el.style.display !== 'none') el.style.display = 'none';
                });
                fullscreenButtons.forEach(el => {
                    if (el.style.display !== 'none') el.style.display = 'none';
                });
            }, 2000);
        }

        // Функция для поиска местоположения пользователя
        function findMyLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                        const userLocation = [position.coords.latitude, position.coords.longitude];
                        
                        // Центрируем карту на местоположении пользователя
                        map.setCenter(userLocation, 15);
                        
                        // Добавляем метку местоположения пользователя
                        const userPlacemark = new ymaps.Placemark(userLocation, {
                            hintContent: 'Ваше местоположение',
                            balloonContent: 'Вы здесь!'
                        }, {
                            preset: 'islands#blueCircleIcon'
                        });
                        
                        map.geoObjects.add(userPlacemark);
                    },
                    function(error) {
                        alert('Не удалось определить ваше местоположение. Проверьте настройки геолокации.');
                        console.error('Geolocation error:', error);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            } else {
                alert('Ваш браузер не поддерживает геолокацию');
            }
        }

        // Инициализация карты после загрузки API
        ymaps.ready(initMap);
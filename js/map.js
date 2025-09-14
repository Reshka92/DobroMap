let map;
let placingMode = false;
let currentPlacemark = null;

// Инициализация карты
function initMap() {
    const center = [44.61665, 33.52536];

    map = new ymaps.Map("map", {
        center: center,
        zoom: 12
    });

    // Управление элементами карты
    map.controls.remove('trafficControl');
    map.controls.remove('fullscreenControl');
    map.controls.remove('rulerControl');
    map.controls.remove('geolocationControl');

    map.controls.add('typeSelector', {
        position: { bottom: '100px', left: '10px' }
    });

    map.controls.add('searchControl', {
        position: { bottom: '40px', left: '10px' },
        options: { provider: 'yandex#search' }
    });

    map.controls.add('zoomControl', {
        position: { bottom: '40px', right: '10px' },
        options: { size: 'small' }
    });

    // Скрываем элементы Яндекса
    hideYandexElements();
    map.events.add('boundschange', hideYandexElements);
    setInterval(hideYandexElements, 2000);

    // Обработка клика по кнопке "Добавить метку"
    const markerBtn = document.getElementById("addMarkerBtn");
    if (markerBtn) {
        markerBtn.addEventListener("click", function() {
            if (!window.userStatus || !window.userStatus.isLoggedIn) {
                alert("Для добавления метки необходимо авторизоваться");
                return;
            }
            togglePlacingMode();
        });
    }

    // Обработка клика по карте
    map.events.add("click", handleMapClick);

    // Загружаем сохранённые метки
    loadExistingMarkers();
}

// Переключение режима размещения метки
function togglePlacingMode() {
    placingMode = !placingMode;
    
    if (placingMode) {
        map.container.getElement().style.cursor = "crosshair";
        document.getElementById("addMarkerBtn").textContent = "Отменить размещение";
        document.getElementById("addMarkerBtn").style.backgroundColor = "#ff4444";
    } else {
        map.container.getElement().style.cursor = "default";
        document.getElementById("addMarkerBtn").textContent = "Добавить метку";
        document.getElementById("addMarkerBtn").style.backgroundColor = "";
        
        // Удаляем временную метку если она есть
        if (currentPlacemark) {
            map.geoObjects.remove(currentPlacemark);
            currentPlacemark = null;
        }
    }
}

// Обработка клика по карте
function handleMapClick(e) {
    if (!placingMode) return;

    const coords = e.get("coords");
    
    // Удаляем предыдущую временную метку
    if (currentPlacemark) {
        map.geoObjects.remove(currentPlacemark);
    }
    
    // Создаем новую метку
    currentPlacemark = new ymaps.Placemark(coords, {
        hintContent: 'Новая метка',
        balloonContent: 'Заполните информацию о метке'
    }, {
        preset: 'islands#blueDotIcon'
    });
    
    map.geoObjects.add(currentPlacemark);
    
    // Показываем форму
    showMarkerForm(coords);
}

// Скрытие элементов Яндекса
function hideYandexElements() {
    const selectors = [
        '.ymaps-2-1-79-copyrights-pane',
        '.ymaps-2-1-79-map-copyrights-promo',
        '.ymaps-2-1-79-copyright__wrap',
        '.ymaps-2-1-79-open-button',
        '.ymaps-2-1-79-create-route-button',
        '.ymaps-2-1-79-fullscreen-button'
    ];

    selectors.forEach(selector => {
        document.querySelectorAll(selector).forEach(el => {
            el.style.display = 'none';
        });
    });
}

// Запуск карты
ymaps.ready(initMap);
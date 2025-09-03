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
    map.controls.remove('geolocationControl');
    
    // Добавляем кнопку слоев (вид со спутника) - левый нижний угол
    map.controls.add('typeSelector', {
        position: {
            top: 'auto',
            bottom: '100px',
            left: '10px',
            right: 'auto'
        }
    });
    
    // Добавляем поиск - левый нижний угол
    map.controls.add('searchControl', {
        position: {
            top: 'auto',
            bottom: '40px',
            left: '10px',
            right: 'auto'
        },
        options: {
            provider: 'yandex#search'
        }
    });
    
    // Добавляем масштаб - правый нижний угол
    map.controls.add('zoomControl', {
        position: {
            top: 'auto',
            bottom: '40px',
            left: 'auto',
            right: '10px'
        },
        options: {
            size: 'small'
        }
    });

    // Скрываем элементы Яндекса после загрузки карты
    map.events.add('load', function() {
        hideYandexElements();
        // Периодически проверяем, не появились ли элементы снова
        setInterval(hideYandexElements, 2000);
    });
}

// Функция для скрытия элементов Яндекса
function hideYandexElements() {
    const elementsToHide = [
        '.ymaps-2-1-79-copyrights-pane',
        '.ymaps-2-1-79-map-copyrights-promo',
        '.ymaps-2-1-79-copyright__wrap',
        '.ymaps-2-1-79-open-button',
        '.ymaps-2-1-79-create-route-button',
        '.ymaps-2-1-79-fullscreen-button'
    ];
    
    elementsToHide.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            el.style.display = 'none';
        });
    });
}

// Инициализация карты после загрузки API
ymaps.ready(initMap);
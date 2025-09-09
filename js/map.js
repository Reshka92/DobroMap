let map;
let placingMode = false;

// Инициализация карты
function initMap() {
    const center = [44.61665, 33.52536];

    map = new ymaps.Map("map", {
        center: center,
        zoom: 12
    });

    // Удаляем стандартные элементы управления
    map.controls.remove('trafficControl');
    map.controls.remove('fullscreenControl');
    map.controls.remove('rulerControl');
    map.controls.remove('geolocationControl');

    // Добавляем нужные элементы
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
    map.events.add('boundschange', function () {
        hideYandexElements();
    });
    setInterval(hideYandexElements, 2000);

    // Обработка клика по кнопке "Добавить метку"
    const markerBtn = document.getElementById("addMarkerBtn");
    if (markerBtn) {
        markerBtn.addEventListener("click", () => {
            placingMode = true;
            map.container.getElement().style.cursor = "crosshair";
        });
    }

    // Обработка клика по карте
    map.events.add("click", function (e) {
        if (!placingMode) return;

        placingMode = false;
        map.container.getElement().style.cursor = "default";

        const coords = e.get("coords");
        showMarkerForm(coords);
    });

    // Загружаем сохранённые метки
    loadExistingMarkers();
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

// Показ формы над меткой
function showMarkerForm(coords) {
    const placemark = new ymaps.Placemark(coords, {}, { draggable: false });
    map.geoObjects.add(placemark);

    const formHtml = `
        <div style="width:250px">
            <label>Описание:<br><textarea id="desc" rows="2"></textarea></label><br>
            <label>Сколько человек:<br><input type="number" id="people" min="1"></label><br>
            <label>Дата:<br><input type="date" id="date"></label><br>
            <label>Время:<br><input type="time" id="time"></label><br>
            <button onclick="submitMarker(${coords[0]}, ${coords[1]})">Сохранить</button>
        </div>
    `;

    placemark.properties.set("balloonContent", formHtml);
    placemark.balloon.open();
}

// Отправка метки на сервер
function submitMarker(lat, lon) {
    const desc = document.getElementById("desc").value;
    const people = document.getElementById("people").value;
    const date = document.getElementById("date").value;
    const time = document.getElementById("time").value;

    if (!desc || !people || !date || !time) {
        alert("Пожалуйста, заполните все поля.");
        return;
    }

    fetch("processes/save_marker.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ lat, lon, desc, people, date, time })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Метка сохранена!");
        } else {
            alert("Ошибка: " + data.message);
        }
    })
    .catch(err => {
        console.error("Ошибка:", err);
        alert("Ошибка соединения с сервером.");
    });
}

// Загрузка всех меток
function loadExistingMarkers() {
    fetch("processes/load_markers.php")
        .then(res => res.json())
        .then(data => {
            data.forEach(marker => {
                const placemark = new ymaps.Placemark([marker.lat, marker.lon], {
                    balloonContent: `
                        <strong>${marker.desc}</strong><br>
                        Людей нужно: ${marker.people}<br>
                        Дата: ${marker.date}<br>
                        Время: ${marker.time}
                    `
                });
                map.geoObjects.add(placemark);
            });
        })
        .catch(err => console.error("Ошибка загрузки меток:", err));
}

// Запуск карты
ymaps.ready(initMap);

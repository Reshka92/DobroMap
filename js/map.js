let map;
let placingMode = false;
let currentPlacemark = null;
let existingMarkers = [];

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

// Показ формы над меткой
function showMarkerForm(coords) {
    const formHtml = `
        <div style="width:250px; padding:10px;">
            <h3 style="margin-top:0;">Добавить мероприятие</h3>
            <label>Описание:</label>
            <input type="text" id="desc" style="width:100%; margin-bottom:10px;" placeholder="Опишите мероприятие">
            
            <label>Сколько человек нужно:</label>
            <input type="number" id="people" min="1" style="width:100%; margin-bottom:10px;" placeholder="Количество человек">
            
            <label>Дата:</label>
            <input type="date" id="date" style="width:100%; margin-bottom:10px;" value="${new Date().toISOString().split('T')[0]}">
            
            <label>Время:</label>
            <input type="time" id="time" style="width:100%; margin-bottom:15px;" value="12:00">

            <div style="display:flex; justify-content:space-between;">
                <button onclick="cancelMarker()" style="background:#ccc; padding:5px 10px; border:none; border-radius:3px;">Отмена</button>
                <button onclick="submitMarker(${coords[0]}, ${coords[1]})" style="background:#4CAF50; color:white; padding:5px 10px; border:none; border-radius:3px;">Сохранить</button>
            </div>
        </div>
    `;

    // Открываем балун на метке
    currentPlacemark.properties.set('balloonContent', formHtml);
    currentPlacemark.balloon.open();
}

// Отмена добавления метки
function cancelMarker() {
    placingMode = false;
    map.container.getElement().style.cursor = "default";
    document.getElementById("addMarkerBtn").textContent = "Добавить метку";
    document.getElementById("addMarkerBtn").style.backgroundColor = "";
    
    if (currentPlacemark) {
        map.geoObjects.remove(currentPlacemark);
        currentPlacemark = null;
    }
}

// Отправка метки на сервер
function submitMarker(lat, lon) {
    const desc = document.getElementById("desc").value;
    const people = document.getElementById("people").value;
    const date = document.getElementById("date").value;
    const time = document.getElementById("time").value;

    console.log("Отправляемые данные:", {desc, people, date, time});

    if (!desc || !people || !date || !time) {
        alert("Пожалуйста, заполните все поля.");
        return;
    }

    fetch("../processes/save_marker.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ 
            lat: lat, 
            lon: lon, 
            desc: desc, 
            people: people, 
            date: date, 
            time: time 
        })
    })
    .then(res => {
        console.log("Статус ответа:", res.status);
        return res.json();
    })
    .then(data => {
        console.log("Ответ сервера:", data);
        if (data.success) {
            alert("Метка успешно сохранена!");
            cancelMarker();
            loadExistingMarkers();
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
    console.log("Загрузка меток...");
    
    // Очищаем существующие метки
    clearExistingMarkers();

    fetch("../processes/load_markers.php")
        .then(res => {
            if (!res.ok) {
                throw new Error('Ошибка загрузки меток: ' + res.status);
            }
            return res.json();
        })
        .then(data => {
            console.log("Метки загружены:", data);
            
            if (!data || data.length === 0) {
                console.log("Нет меток для отображения");
                return;
            }
            
            data.forEach(marker => {
                if (!marker.lat || !marker.lon) {
                    console.warn("Пропущена метка с невалидными координатами:", marker);
                    return;
                }
                
                const placemark = createMarker(marker);
                map.geoObjects.add(placemark);
                existingMarkers.push(placemark);
            });
            
            console.log("Метки успешно добавлены на карту. Всего: " + existingMarkers.length);
        })
        .catch(err => {
            console.error("Ошибка загрузки меток:", err);
        });
}

// Создание метки с улучшенным отображением
function createMarker(marker) {
    console.log("Создание метки:", marker); // Отладочная информация
    
    const progress = Math.min(100, ((marker.people_joined || 0) / (marker.people_needed || 1)) * 100);
    const progressColor = progress >= 100 ? '#4CAF50' : '#ff6b6b';
    
    const placemark = new ymaps.Placemark(
        [parseFloat(marker.lat), parseFloat(marker.lon)], 
        {
            balloonContent: `
                <div style="padding:15px; max-width:350px;">
                    <h3 style="margin-top:0; color:#2c3e50;">${marker.description || 'Мероприятие'}</h3>
                    <p><strong>Организатор:</strong> ${marker.user_name || 'Аноним'}</p>
                    <p><strong>Нужно волонтеров:</strong> ${marker.people_needed || 0}</p>
                    <p><strong>Уже присоединилось:</strong> ${marker.people_joined || 0}</p>
                    <p><strong>Дата:</strong> ${marker.event_date || 'Не указана'}</p>
                    <p><strong>Время:</strong> ${marker.event_time || 'Не указано'}</p>
                    
                    <div style="margin:10px 0; background:#eee; border-radius:5px;">
                        <div style="height:20px; background:${progressColor}; border-radius:5px; width:${progress}%"></div>
                    </div>
                    
                    ${marker.can_join ? `
                        <button onclick="joinEvent(${marker.id})" 
                                style="background:#4CAF50; color:white; padding:10px; border:none; border-radius:5px; width:100%; cursor:pointer;">
                            Присоединиться
                        </button>
                    ` : marker.user_joined ? `
                        <button style="background:#2196F3; color:white; padding:10px; border:none; border-radius:5px; width:100%;">
                            Вы участвуете ✓
                        </button>
                    ` : `
                        <button style="background:#ccc; padding:10px; border:none; border-radius:5px; width:100%;">
                            ${progress >= 100 ? 'Мест нет' : 'Ошибка'}
                        </button>
                    `}
                </div>
            `,
            hintContent: `${marker.description || 'Мероприятие'} (${marker.people_joined || 0}/${marker.people_needed || 0})`
        }, 
        {
            preset: (marker.people_joined || 0) >= (marker.people_needed || 1) ? 'islands#greenIcon' : 
                   (marker.people_joined || 0) > 0 ? 'islands#blueIcon' : 'islands#redIcon',
            balloonCloseButton: true,
            hideIconOnBalloonOpen: false
        }
    );
    
    return placemark;
}

// Функция присоединения к событию
function joinEvent(eventId) {
    if (!window.userStatus || !window.userStatus.isLoggedIn) {
        alert("Для присоединения к мероприятию необходимо авторизоваться");
        return;
    }
    
    if (!confirm('Вы уверены, что хотите присоединиться к этому мероприятию?')) return;
    
    fetch("../processes/join_event.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ event_id: eventId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Вы успешно присоединились к мероприятию!");
            loadExistingMarkers(); // Обновляем метки
        } else {
            alert("Ошибка: " + data.message);
        }
    })
    .catch(err => {
        console.error("Ошибка:", err);
        alert("Ошибка соединения");
    });
}

// Очистка существующих меток
function clearExistingMarkers() {
    existingMarkers.forEach(marker => {
        map.geoObjects.remove(marker);
    });
    existingMarkers = [];
}

// Запуск карты
ymaps.ready(initMap);
let existingMarkers = [];

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
    .then(res => res.json())
    .then(data => {
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
    // Очищаем существующие метки
    clearExistingMarkers();

    fetch("../processes/load_markers.php")
        .then(res => res.json())
        .then(data => {
            console.log("Данные с сервера:", data);
            
            if (!data || data.length === 0) return;
            
            data.forEach(marker => {
                if (!marker.lat || !marker.lon) return;
                
                const placemark = createMarker(marker);
                map.geoObjects.add(placemark);
                existingMarkers.push(placemark);
            });
        })
        .catch(err => {
            console.error("Ошибка загрузки меток:", err);
        });
}

// Создание метки с улучшенным отображением
function createMarker(marker) {
    console.log("Создание метки:", marker);
    
    const peopleJoined = parseInt(marker.people_joined) || 0;
    const peopleNeeded = parseInt(marker.people_needed) || 1;
    
    const progress = Math.min(100, (peopleJoined / peopleNeeded) * 100);
    const progressColor = progress >= 100 ? '#4CAF50' : '#ff6b6b';
    
    // Определяем, какую кнопку показывать
    let buttonHtml = '';
    if (marker.is_creator) {
        buttonHtml = `<button style="background:#4CAF50; color:white; padding:10px; border:none; border-radius:5px; width:100%;">Вы организатор</button>`;
    } else if (marker.can_join) {
        buttonHtml = `<button onclick="joinEvent(${marker.id})" style="background:#4CAF50; color:white; padding:10px; border:none; border-radius:5px; width:100%; cursor:pointer;">Присоединиться</button>`;
    } else if (marker.user_joined) {
        buttonHtml = `<button style="background:#2196F3; color:white; padding:10px; border:none; border-radius:5px; width:100%;">Вы участвуете ✓</button>`;
    } else {
        buttonHtml = `<button style="background:#ccc; padding:10px; border:none; border-radius:5px; width:100%;">${progress >= 100 ? 'Мест нет' : 'Ошибка'}</button>`;
    }
    
    const placemark = new ymaps.Placemark(
        [parseFloat(marker.lat), parseFloat(marker.lon)], 
        {
            balloonContent: `
                <div style="padding:15px; max-width:350px;">
                    <h3 style="margin-top:0; color:#2c3e50;">${marker.description || 'Мероприятие'}</h3>
                    <p><strong>Организатор:</strong> ${marker.user_name || 'Аноним'}</p>
                    <p><strong>Нужно волонтеров:</strong> ${peopleNeeded}</p>
                    <p><strong>Уже присоединилось:</strong> ${peopleJoined}</p>
                    <p><strong>Дата:</strong> ${marker.event_date || 'Не указана'}</p>
                    <p><strong>Время:</strong> ${marker.event_time || 'Не указано'}</p>
                    
                    <div style="margin:10px 0; background:#eee; border-radius:5px;">
                        <div style="height:20px; background:${progressColor}; border-radius:5px; width:${progress}%"></div>
                    </div>
                    
                    ${buttonHtml}
                </div>
            `,
            hintContent: `${marker.description} (${peopleJoined}/${peopleNeeded})`
        }, 
        {
            preset: progress >= 100 ? 'islands#greenIcon' : 
                   peopleJoined > 0 ? 'islands#blueIcon' : 'islands#redIcon',
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
            loadExistingMarkers();
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
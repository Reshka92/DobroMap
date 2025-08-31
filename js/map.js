function initMap() {
  const center = [44.61665, 33.52536]; 

  const map = new ymaps.Map("map", {
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
      bottom: '40px',
      left: '10px',
      right: 'auto'
    }
  });
  
  // Поиск - левый нижний угол над кнопкой слоев
  map.controls.add('searchControl', {
    position: {
      top: 'auto',
      bottom: '100px',
      left: '10px',
      right: 'auto'
    }
  });
  
  // Масштаб - правый нижний угол
  map.controls.add('zoomControl', {
    position: {
      top: 'auto',
      bottom: '40px',
      left: 'auto',
      right: '10px'
    }
  });
  
  // Полноэкранный режим - правый нижний угол над масштабом
  map.controls.add('fullscreenControl', {
    position: {
      top: 'auto',
      bottom: '100px',
      left: 'auto',
      right: '10px'
    }
  });
}

ymaps.ready(initMap);
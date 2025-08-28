function initMap() {
  const center = [44.61665, 33.52536]; 

  const map = new ymaps.Map("map", {
    center: center,
    zoom: 12
  });
   map.controls.remove('trafficControl');      // Пробки
  map.controls.remove('fullscreenControl');   // Полноэкранный режим
}

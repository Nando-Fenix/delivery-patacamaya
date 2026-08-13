import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import * as bootstrap from 'bootstrap';
import './echo';

window.bootstrap = bootstrap;
window.L = L;

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch((error) => {
            console.warn('No se pudo registrar el service worker.', error);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.location-map').forEach((element) => {
        const latInput = document.getElementById(element.dataset.latInput);
        const lngInput = document.getElementById(element.dataset.lngInput);
        const initial = latInput?.value && lngInput?.value ? [Number(latInput.value), Number(lngInput.value)] : [-17.2350, -67.9210];
        const map = L.map(element).setView(initial, latInput?.value ? 16 : 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 }).addTo(map);
        let marker = latInput?.value ? L.marker(initial, { draggable: true }).addTo(map) : null;
        const setPoint = (lat, lng) => { if (!marker) { marker = L.marker([lat, lng], { draggable: true }).addTo(map); marker.on('dragend', () => setPoint(marker.getLatLng().lat, marker.getLatLng().lng)); } else marker.setLatLng([lat, lng]); latInput.value = Number(lat).toFixed(7); lngInput.value = Number(lng).toFixed(7); };
        map.on('click', (event) => setPoint(event.latlng.lat, event.latlng.lng));
        marker?.on('dragend', () => setPoint(marker.getLatLng().lat, marker.getLatLng().lng));
        const button = element.parentElement.querySelector('.use-geolocation'); const status = element.parentElement.querySelector('.geolocation-status');
        button?.addEventListener('click', () => { if (!navigator.geolocation) { status.textContent = 'Tu navegador no admite geolocalización. Selecciona el punto en el mapa.'; return; } status.textContent = 'Obteniendo ubicación…'; navigator.geolocation.getCurrentPosition((position) => { const { latitude, longitude } = position.coords; setPoint(latitude, longitude); map.setView([latitude, longitude], 17); status.textContent = 'Ubicación obtenida correctamente.'; }, (error) => { status.textContent = error.code === 1 ? 'Permiso rechazado. Selecciona el punto manualmente.' : error.code === 2 ? 'Ubicación no disponible. Intenta nuevamente o usa el mapa.' : 'La solicitud tardó demasiado. Usa el mapa o vuelve a intentar.'; }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }); });
        setTimeout(() => map.invalidateSize(), 100);
    });
});
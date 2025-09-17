// resources/js/bootstrap.js
import axios from 'axios';

window.axios = axios;

// Aseguramos que axios envie cookies (importante con Vite dev server)
window.axios.defaults.withCredentials = true;

// Cabecera AJAX estándar
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Token CSRF desde <meta name="csrf-token">
const tokenMeta = document.head.querySelector('meta[name="csrf-token"]');

if (tokenMeta) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.content;
} else {
  console.error('CSRF token not found: check your <meta name="csrf-token"> in blade layout.');
}

// Opcional: set baseURL si tu API está en otra ruta (p.ej. si estás en subfolder)
// window.axios.defaults.baseURL = '/';

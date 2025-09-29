// resources/js/bootstrap.js
import axios from 'axios';

//
// ---------- Axios base ----------
//
window.axios = axios;

// Enviar cookies en mismas-origin (necesario con Vite/SPA)
window.axios.defaults.withCredentials = true;

// Cabecera AJAX estándar
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// (Opcional) Si tu API vive en el mismo dominio, puedes dejarlo así.
// Si usas un subpath o dominio distinto, define baseURL:
// window.axios.defaults.baseURL = 'http://localhost:8000';

//
// ---------- CSRF inicial desde <meta> ----------
//
const tokenMeta = document.head.querySelector('meta[name="csrf-token"]');

if (tokenMeta?.content) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.content;
} else {
  console.error(
    'CSRF token not found. Asegúrate de tener <meta name="csrf-token" content="{{ csrf_token() }}"> en tu layout.'
  );
}

//
// ---------- Auto-refresh CSRF en 419 (sin Sanctum) ----------
//  - Reintenta automáticamente la request que falló por 419,
//    refrescando el token desde GET /csrf/refresh.
//  - Evita bucles con config._retried.
//  - Cola las requests si múltiples 419 ocurren a la vez.
//
let _isRefreshingCsrf = false;
let _retryQueue = [];

/** Vacía la cola reintentando (o rechazando) las solicitudes pendientes */
function _flushQueue(success) {
  _retryQueue.forEach(({ resolve, reject, config }) => {
    success ? resolve(window.axios(config)) : reject();
  });
  _retryQueue = [];
}

window.axios.interceptors.response.use(
  (response) => response,
  async (error) => {
    const { response, config } = error || {};

    // Si no hay respuesta, o no es 419, o ya reintentamos, devolvemos el error
    if (!response || response.status !== 419 || config?._retried) {
      return Promise.reject(error);
    }

    // Si ya hay un refresh en curso, encolamos y esperamos a que termine
    if (_isRefreshingCsrf) {
      return new Promise((resolve, reject) => {
        _retryQueue.push({ resolve, reject, config });
      });
    }

    _isRefreshingCsrf = true;

    try {
      // IMPORTANTE: crea la ruta en web.php:
      // Route::get('/csrf/refresh', fn() => response()->json(['token' => csrf_token()]))->name('csrf.refresh');
      const { data } = await window.axios.get('/csrf/refresh');
      const newToken = data?.token;

      if (newToken) {
        // Actualiza header global y el de la request que vamos a reintentar
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
        config.headers = { ...(config.headers || {}), 'X-CSRF-TOKEN': newToken };

        // También refrescamos el <meta> por si otras libs lo leen
        let meta = document.head.querySelector('meta[name="csrf-token"]');
        if (!meta) {
          meta = document.createElement('meta');
          meta.setAttribute('name', 'csrf-token');
          document.head.appendChild(meta);
        }
        meta.setAttribute('content', newToken);
      }

      // Marca como reintentada para evitar bucles
      config._retried = true;

      // Reintenta la request original
      const retry = window.axios(config);

      // Despierta la cola con éxito
      _flushQueue(true);

      return retry;
    } catch (e) {
      // Si falló el refresh, rechazamos todo
      _flushQueue(false);
      return Promise.reject(error);
    } finally {
      _isRefreshingCsrf = false;
    }
  }
);

// --- Fin de bootstrap.js ---

// resources/js/app.js
import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: title => `${title} - ${appName}`,
  resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const vuapp = createApp({ render: () => h(App, props) })
      .use(plugin);

    // Si @routes (Ziggy) está inyectado en la plantilla blade, habrá una variable global `Ziggy`.
    // Pasamos esa configuración al plugin para que route(...) funcione correctamente.
    try {
      // eslint-disable-next-line no-undef
      if (typeof Ziggy !== 'undefined') {
        vuapp.use(ZiggyVue, Ziggy);
      } else {
        // Si Ziggy no existe como global, aún podemos registrar el plugin sin datos.
        vuapp.use(ZiggyVue);
        console.warn('Ziggy global not found. Make sure you have @routes in your blade layout.');
      }
    } catch (e) {
      // fallback seguro
      vuapp.use(ZiggyVue);
      console.warn('ZiggyVue loaded without Ziggy config:', e);
    }

    vuapp.mount(el);
  },
});

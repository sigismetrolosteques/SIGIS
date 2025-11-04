<script setup>
import { ref, onMounted, watch, onBeforeUnmount, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  solicitudes: Object,
  totales: Object,
  gerencias: Array,
  products: Array,
  filters: Object,
});

const form = useForm({
  from: props.filters.from || '',
  to: props.filters.to || '',
  status: props.filters.status || '',
  from_gerencia_id: props.filters.from_gerencia_id || '',
  product_id: props.filters.product_id || '',
  per_page: props.filters.per_page || 15,
});

function formatDate(value) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}`;
}

// Construir objeto de params (evita mandar keys vacías si quieres)
function buildParamsObject() {
  return {
    from: form.from || '',
    to: form.to || '',
    status: form.status || '',
    from_gerencia_id: form.from_gerencia_id || '',
    product_id: form.product_id || '',
    per_page: form.per_page || '',
  };
}

// Aplicar filtros: request Inertia con params
function applyFilters() {
  const params = buildParamsObject();
  router.get(route('reportes.solicitudes'), params, { preserveState: true, preserveScroll: true });
}

// Reset filtros
function resetFilters() {
  form.reset('from', 'to', 'status', 'from_gerencia_id', 'product_id', 'per_page');
  router.get(route('reportes.solicitudes'), {}, { preserveState: true });
}

// Exportar PDF (abre nueva pestaña con filtros)
function exportPdf() {
  const params = new URLSearchParams(buildParamsObject()).toString();
  const url = route('reportes.solicitudes.pdf') + (params ? ('?' + params) : '');
  window.open(url, '_blank');
}

/* --- Charts --- */
const statusChartRef = ref(null);
const productChartRef = ref(null);
let statusChart = null;
let productChart = null;

function destroyCharts() {
  try { if (statusChart) statusChart.destroy(); } catch(e){}
  try { if (productChart) productChart.destroy(); } catch(e){}
  statusChart = null;
  productChart = null;
}

function renderCharts(solicitudesData = []) {
  destroyCharts();

  // Estado
  const enProceso = solicitudesData.filter(s => !['aprobado', 'rechazado'].includes(s.status)).length;
  const aprobados = solicitudesData.filter(s => s.status === 'aprobado').length;
  const rechazados = solicitudesData.filter(s => s.status === 'rechazado').length;

  if (statusChartRef.value) {
    statusChart = new Chart(statusChartRef.value, {
      type: 'doughnut',
      data: {
        labels: ['En proceso', 'Aprobado', 'Rechazado'],
        datasets: [{ data: [enProceso, aprobados, rechazados], backgroundColor: ['#F59E0B','#10B981','#EF4444'] }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }

  // Por producto (lineal)
  const byProduct = {};
  solicitudesData.forEach(s => {
    const name = s.product?.name || 'Sin producto';
    byProduct[name] = (byProduct[name] || 0) + 1;
  });

  if (productChartRef.value) {
    productChart = new Chart(productChartRef.value, {
      type: 'bar',
      data: {
        labels: Object.keys(byProduct),
        datasets: [{
          label: 'Solicitudes',
          data: Object.values(byProduct),
          backgroundColor: '#3B82F6'
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        plugins: { legend: { display: false } }
      }
    });
  }
}

// render inicial
onMounted(() => {
  renderCharts(props.solicitudes?.data || []);
});

// volver a renderizar cuando los datos del servidor cambian (filtros/paginación)
watch(() => props.solicitudes?.data, (nv) => renderCharts(nv || []));

// destruir al desmontar
onBeforeUnmount(() => destroyCharts());
</script>

<template>
  <AdminLayout title="Reportes - Solicitudes">
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Reportes: Solicitudes</h1>
        <div class="flex gap-2">
          <button @click="exportPdf" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Exportar PDF</button>
        </div>
      </div>

      <!-- filtros -->
      <div class="bg-white p-4 rounded shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm mb-1">Desde</label>
            <input v-model="form.from" type="date" class="w-full border rounded px-2 py-1" />
          </div>
          <div>
            <label class="block text-sm mb-1">Hasta</label>
            <input v-model="form.to" type="date" class="w-full border rounded px-2 py-1" />
          </div>
          <div>
            <label class="block text-sm mb-1">Estado</label>
            <select v-model="form.status" class="w-full border rounded px-2 py-1">
              <option value="">-- Todos --</option>
              <option value="pendiente">Normalización</option>
              <option value="vp_mantenimiento">VP. Mantenimiento</option>
              <option value="vp_soporte">VP. Soporte</option>
              <option value="administracion">Administración</option>
              <option value="compras">Compras</option>
              <option value="normalizacion_final">Normalización (Entrega)</option>
              <option value="aprobado">Aprobado</option>
              <option value="rechazado">Rechazado</option>
            </select>
          </div>

          <div>
            <label class="block text-sm mb-1">Producto</label>
            <select v-model="form.product_id" class="w-full border rounded px-2 py-1">
              <option value="">-- Todos --</option>
              <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm mb-1">Destino</label>
            <select v-model="form.from_gerencia_id" class="w-full border rounded px-2 py-1">
              <option value="">-- Todos --</option>
              <option v-for="g in gerencias" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm mb-1">Por página</label>
            <select v-model="form.per_page" class="w-full border rounded px-2 py-1">
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>

          <div class="flex items-end gap-2">
            <button @click="applyFilters" class="px-3 py-2 bg-green-600 text-white rounded">Aplicar</button>
            <button @click="resetFilters" class="px-3 py-2 bg-gray-300 rounded">Reset</button>
          </div>
        </div>
      </div>

      <!-- resumen -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded shadow">
          <div class="text-sm text-gray-500">Total solicitudes</div>
          <div class="text-2xl font-bold">{{ totales.total }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-sm text-gray-500">En proceso</div>
          <div class="text-2xl font-bold">{{ totales.en_proceso }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-sm text-gray-500">Aprobado</div>
          <div class="text-2xl font-bold">{{ totales.aprobado }}</div>
        </div>
      </div>

      <!-- charts -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-4 rounded shadow">
          <h3 class="font-semibold mb-2">Solicitudes por estado</h3>
          <canvas ref="statusChartRef"></canvas>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <h3 class="font-semibold mb-2">Solicitudes por producto (lineal)</h3>
          <canvas ref="productChartRef"></canvas>
        </div>
      </div>

      <!-- tabla -->
      <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-100 border-b">
            <tr>
              <th class="py-2 px-3 text-left">Código</th>
              <th class="py-2 px-3 text-left">Producto</th>
              <th class="py-2 px-3 text-left">Destino</th>
              <th class="py-2 px-3 text-left">Cantidad</th>
              <th class="py-2 px-3 text-left">Estado</th>
              <th class="py-2 px-3 text-left">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in solicitudes.data" :key="s.id" class="border-b">
              <td class="py-2 px-3 font-mono">{{ s.code }}</td>
              <td class="py-2 px-3">{{ s.product?.name }}</td>
              <td class="py-2 px-3">{{ s.fromGerencia?.name || s.from_gerencia?.name }}</td>
              <td class="py-2 px-3">{{ s.quantity ?? '-' }}</td>
              <td class="py-2 px-3 capitalize">{{ s.status }}</td>
              <td class="py-2 px-3">{{ formatDate(s.created_at) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="p-4">
          <Pagination :links="solicitudes.links" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

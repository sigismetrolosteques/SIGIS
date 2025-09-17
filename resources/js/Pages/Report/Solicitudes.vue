<script setup>
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

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
  to_gerencia_id: props.filters.to_gerencia_id || '',
  product_id: props.filters.product_id || '',
  per_page: props.filters.per_page || 15,
});

function applyFilters() {
  form.get(route('reportes.solicitudes.index'), { preserveState: true, preserveScroll: true });
}

function resetFilters() {
  form.reset('from', 'to', 'status', 'from_gerencia_id', 'to_gerencia_id', 'product_id');
  form.get(route('reportes.solicitudes.index'), { preserveState: true });
}

function exportCsv() {
  // construimos URL con query params e iniciamos descarga (usa Ziggy route)
  const params = new URLSearchParams({
    from: form.from || '',
    to: form.to || '',
    status: form.status || '',
    from_gerencia_id: form.from_gerencia_id || '',
    to_gerencia_id: form.to_gerencia_id || '',
    product_id: form.product_id || '',
  }).toString();

  window.location.href = route('reportes.solicitudes.export') + (params ? ('?' + params) : '');
}
</script>

<template>
  <AdminLayout title="Reportes - Solicitudes">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Reportes: Solicitudes</h1>
        <div>
          <button @click="exportCsv" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Exportar CSV</button>
        </div>
      </div>

      <!-- filtros -->
      <div class="bg-white p-4 rounded shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Desde</label>
            <input v-model="form.from" type="date" class="w-full border rounded px-2 py-1" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Hasta</label>
            <input v-model="form.to" type="date" class="w-full border rounded px-2 py-1" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Estado</label>
            <select v-model="form.status" class="w-full border rounded px-2 py-1">
              <option value="">-- Todos --</option>
              <option value="pendiente">Pendiente</option>
              <option value="aprobado">Aprobado</option>
              <option value="rechazado">Rechazado</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Producto</label>
            <select v-model="form.product_id" class="w-full border rounded px-2 py-1">
              <option value="">-- Todos --</option>
              <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">De Gerencia</label>
            <select v-model="form.from_gerencia_id" class="w-full border rounded px-2 py-1">
              <option value="">-- Todas --</option>
              <option v-for="g in gerencias" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">A Gerencia</label>
            <select v-model="form.to_gerencia_id" class="w-full border rounded px-2 py-1">
              <option value="">-- Todas --</option>
              <option v-for="g in gerencias" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Por página</label>
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
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded shadow">
          <div class="text-sm text-gray-500">Total solicitudes</div>
          <div class="text-2xl font-bold">{{ totales.total }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-sm text-gray-500">Pendiente</div>
          <div class="text-2xl font-bold">{{ totales.pendiente }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-sm text-gray-500">Aprobado</div>
          <div class="text-2xl font-bold">{{ totales.aprobado }}</div>
        </div>
      </div>

      <!-- tabla -->
      <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-100 border-b">
            <tr>
              <th class="py-2 px-3 text-left">Código</th>
              <th class="py-2 px-3 text-left">Producto</th>
              <th class="py-2 px-3 text-left">De</th>
              <th class="py-2 px-3 text-left">A</th>
              <th class="py-2 px-3 text-left">Cantidad</th>
              <th class="py-2 px-3 text-left">Estado</th>
              <th class="py-2 px-3 text-left">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in solicitudes.data" :key="s.id" class="border-b">
              <td class="py-2 px-3 font-mono">{{ s.code }}</td>
              <td class="py-2 px-3">{{ s.product?.name }}</td>
              <td class="py-2 px-3">{{ s.from_gerencia?.name }}</td>
              <td class="py-2 px-3">{{ s.to_gerencia?.name }}</td>
              <td class="py-2 px-3">{{ s.quantity ?? '-' }}</td>
              <td class="py-2 px-3 capitalize">{{ s.status }}</td>
              <td class="py-2 px-3">{{ s.created_at }}</td>
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

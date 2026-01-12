<script setup>
import { useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  inventories: Object,
  totals: Object,
  filters: Object,
  products: Array,
  tipoMaterialOptions: Array,
});

const form = useForm({
  search: props.filters.search || '',
  from: props.filters.from || '',
  to: props.filters.to || '',
  product_id: props.filters.product_id || '',
  tipo_material: props.filters.tipo_material || '',
  per_page: props.filters.per_page || 15,
});

function buildParams() {
  return {
    search: form.search || '',
    from: form.from || '',
    to: form.to || '',
    product_id: form.product_id || '',
    tipo_material: form.tipo_material || '',
    per_page: form.per_page || '',
  };
}

function applyFilters() {
  router.get(route('reportes.inventario'), buildParams(), { preserveState: true, preserveScroll: true });
}

function resetFilters() {
  form.reset('search', 'from', 'to', 'product_id', 'tipo_material', 'per_page');
  router.get(route('reportes.inventario'), {}, { preserveState: true });
}

function exportPdf() {
  const params = new URLSearchParams(buildParams()).toString();
  const url = route('reportes.inventario.pdf') + (params ? ('?' + params) : '');
  window.open(url, '_blank');
}

function formatDate(value) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

function available(inv) {
  const qty = Number(inv.quantity) || 0;
  const reserved = Number(inv.reserved_quantity) || 0;
  return Math.max(0, qty - reserved);
}
</script>

<template>
  <AdminLayout title="Reportes - Inventario">
    <div class="p-6 space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-brand-charcoal">Reportes: Inventario</h1>
          <p class="text-sm text-gray-600">Detalle de stock, reservas y ubicaciones.</p>
        </div>
        <div class="flex gap-2">
          <button @click="exportPdf" class="px-3 py-2 bg-brand-red text-white rounded hover:bg-brand-wine">Exportar PDF</button>
        </div>
      </div>

      <div class="flex flex-wrap gap-3">
        <Link :href="route('reportes.solicitudes')" class="px-3 py-1.5 rounded-full border border-gray-200 text-sm text-gray-600 hover:bg-brand-gray">
          Solicitudes
        </Link>
        <Link :href="route('reportes.inventario')" class="px-3 py-1.5 rounded-full border border-brand-red bg-brand-red text-white text-sm">
          Inventario
        </Link>
        <Link :href="route('reportes.productos')" class="px-3 py-1.5 rounded-full border border-gray-200 text-sm text-gray-600 hover:bg-brand-gray">
          Productos
        </Link>
      </div>

      <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm mb-1">Buscar</label>
            <input v-model="form.search" type="text" class="w-full border rounded px-2 py-1" placeholder="Producto o ubicación" />
          </div>
          <div>
            <label class="block text-sm mb-1">Desde</label>
            <input v-model="form.from" type="date" class="w-full border rounded px-2 py-1" />
          </div>
          <div>
            <label class="block text-sm mb-1">Hasta</label>
            <input v-model="form.to" type="date" class="w-full border rounded px-2 py-1" />
          </div>
          <div>
            <label class="block text-sm mb-1">Producto</label>
            <select v-model="form.product_id" class="w-full border rounded px-2 py-1">
              <option value="">-- Todos --</option>
              <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm mb-1">Tipo de material</label>
            <select v-model="form.tipo_material" class="w-full border rounded px-2 py-1">
              <option value="">-- Todos --</option>
              <option v-for="t in tipoMaterialOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
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
            <button @click="applyFilters" class="px-3 py-2 bg-brand-red text-white rounded">Aplicar</button>
            <button @click="resetFilters" class="px-3 py-2 bg-brand-gray text-brand-charcoal rounded">Reset</button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded shadow border border-gray-200">
          <div class="text-sm text-gray-500">Total registros</div>
          <div class="text-2xl font-bold text-brand-charcoal">{{ totals.total }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow border border-gray-200">
          <div class="text-sm text-gray-500">Cantidad total</div>
          <div class="text-2xl font-bold text-brand-charcoal">{{ totals.quantity_total }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow border border-gray-200">
          <div class="text-sm text-gray-500">Reservado</div>
          <div class="text-2xl font-bold text-brand-charcoal">{{ totals.reserved_total }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow border border-gray-200">
          <div class="text-sm text-gray-500">Disponible</div>
          <div class="text-2xl font-bold text-brand-red">{{ totals.available_total }}</div>
        </div>
      </div>

      <div class="bg-white shadow rounded overflow-hidden border border-gray-200">
        <table class="w-full text-sm">
          <thead class="bg-brand-gray text-brand-charcoal">
            <tr>
              <th class="py-2 px-3 text-left">Código</th>
              <th class="py-2 px-3 text-left">Producto</th>
              <th class="py-2 px-3 text-left">Cantidad</th>
              <th class="py-2 px-3 text-left">Reservado</th>
              <th class="py-2 px-3 text-left">Disponible</th>
              <th class="py-2 px-3 text-left">Ubicación</th>
              <th class="py-2 px-3 text-left">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inv in inventories.data" :key="inv.id" class="border-b">
              <td class="py-2 px-3 font-mono">{{ inv.product?.code ?? 'N/D' }}</td>
              <td class="py-2 px-3">{{ inv.product?.name ?? 'Producto eliminado' }}</td>
              <td class="py-2 px-3">{{ inv.quantity }}</td>
              <td class="py-2 px-3">{{ inv.reserved_quantity }}</td>
              <td class="py-2 px-3 font-semibold text-brand-red">{{ available(inv) }}</td>
              <td class="py-2 px-3">{{ inv.location ?? 'Sin ubicación' }}</td>
              <td class="py-2 px-3">{{ formatDate(inv.created_at) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="p-4">
          <Pagination :links="inventories.links" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

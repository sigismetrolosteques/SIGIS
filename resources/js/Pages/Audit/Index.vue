<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  audits: Object,
  users: Array,
  filters: Object,
});

const form = useForm({
  q: props.filters.q || '',
  action: props.filters.action || '',
  model: props.filters.model || '',
  user_id: props.filters.user_id || '',
  from: props.filters.from || '',
  to: props.filters.to || '',
  per_page: props.filters.per_page || 20,
});

function applyFilters() {
  form.get(route('auditoria.index'), { preserveState: true, preserveScroll: true });
}

function resetFilters() {
  form.reset('q','action','model','user_id','from','to');
  form.get(route('auditoria.index'), { preserveState: true, preserveScroll: true });
}

function exportCsv() {
  const params = new URLSearchParams({
    q: form.q || '',
    action: form.action || '',
    model: form.model || '',
    user_id: form.user_id || '',
    from: form.from || '',
    to: form.to || '',
  }).toString();

  window.location.href = route('auditoria.export') + (params ? ('?' + params) : '');
}

function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return dateString;
  return new Intl.DateTimeFormat('es-VE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
}
</script>

<template>
  <AdminLayout title="Auditoría">
    <div class="space-y-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Auditoría</h1>
        <button
          @click="exportCsv"
          class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition"
        >
          Exportar CSV
        </button>
      </div>

      <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <input v-model="form.q" placeholder="Buscar..." class="p-2 border rounded w-full" />
          <select v-model="form.action" class="p-2 border rounded w-full">
            <option value="">-- Acción --</option>
            <option value="created">created</option>
            <option value="updated">updated</option>
            <option value="deleted">deleted</option>
          </select>
          <input v-model="form.model" placeholder="Modelo (Solicitud, Product,...)" class="p-2 border rounded w-full" />
          <select v-model="form.user_id" class="p-2 border rounded w-full">
            <option value="">-- Usuario --</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>

          <input v-model="form.from" type="date" class="p-2 border rounded w-full" />
          <input v-model="form.to" type="date" class="p-2 border rounded w-full" />
          <select v-model.number="form.per_page" class="p-2 border rounded w-full">
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>

          <div class="flex flex-wrap items-center gap-2">
            <button
              type="submit"
              class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded whitespace-nowrap transition"
            >
              Filtrar
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded whitespace-nowrap transition"
            >
              Reset
            </button>
          </div>
        </form>
      </div>

      <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-100 border-b text-gray-700">
              <tr>
                <th class="p-2 text-left">#</th>
                <th class="p-2 text-left">Usuario</th>
                <th class="p-2 text-left">Modelo</th>
                <th class="p-2 text-left">ID</th>
                <th class="p-2 text-left">Acción</th>
                <th class="p-2 text-left">IP</th>
                <th class="p-2 text-left">Fecha</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in audits.data" :key="a.id" class="border-b hover:bg-gray-50">
                <td class="p-2">{{ a.id }}</td>
                <td class="p-2">{{ a.user?.name ?? 'Sistema' }}</td>
                <td class="p-2">{{ a.auditable_type.split('\\').pop() }}</td>
                <td class="p-2">{{ a.auditable_id }}</td>
                <td class="p-2 capitalize">{{ a.action }}</td>
                <td class="p-2">{{ a.ip_address ?? 'N/A' }}</td>
                <td class="p-2">{{ formatDate(a.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4">
          <Pagination :links="audits.links" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

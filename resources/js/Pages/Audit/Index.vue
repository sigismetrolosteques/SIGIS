<script setup>
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
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
</script>

<template>
  <AdminLayout title="Auditoría">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Auditoría</h1>
        <div>
          <button @click="exportCsv" class="px-3 py-2 bg-indigo-600 text-white rounded">Exportar CSV</button>
        </div>
      </div>

      <!-- filtros -->
      <div class="bg-white p-4 rounded shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <input v-model="form.q" placeholder="Buscar..." class="p-2 border rounded" />
          <select v-model="form.action" class="p-2 border rounded">
            <option value="">-- Acción --</option>
            <option value="created">created</option>
            <option value="updated">updated</option>
            <option value="deleted">deleted</option>
          </select>
          <input v-model="form.model" placeholder="Modelo (Solicitud, Product,...)" class="p-2 border rounded" />
          <select v-model="form.user_id" class="p-2 border rounded">
            <option value="">-- Usuario --</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>

          <input v-model="form.from" type="date" class="p-2 border rounded" />
          <input v-model="form.to" type="date" class="p-2 border rounded" />
          <select v-model="form.per_page" class="p-2 border rounded">
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>

          <div class="flex items-center gap-2">
            <button @click="applyFilters" class="px-3 py-2 bg-green-600 text-white rounded">Filtrar</button>
            <button @click="resetFilters" class="px-3 py-2 bg-gray-300 rounded">Reset</button>
          </div>
        </div>
      </div>

      <!-- tabla -->
      <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-100 border-b">
            <tr>
              <th class="p-2 text-left">#</th>
              <th class="p-2 text-left">Usuario</th>
              <th class="p-2 text-left">Modelo</th>
              <th class="p-2 text-left">ID</th>
              <th class="p-2 text-left">Acción</th>
              <th class="p-2 text-left">Old</th>
              <th class="p-2 text-left">New</th>
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
              <td class="p-2">{{ a.action }}</td>
              <td class="p-2"><pre class="text-xs max-w-xs overflow-auto">{{ JSON.stringify(a.old_values, null, 2) }}</pre></td>
              <td class="p-2"><pre class="text-xs max-w-xs overflow-auto">{{ JSON.stringify(a.new_values, null, 2) }}</pre></td>
              <td class="p-2">{{ a.ip_address }}</td>
              <td class="p-2">{{ a.created_at }}</td>
            </tr>
          </tbody>
        </table>

        <div class="p-4">
          <Pagination :links="audits.links" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

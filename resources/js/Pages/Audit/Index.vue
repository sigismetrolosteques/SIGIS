<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { EyeIcon, ShieldCheckIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

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

const showDetailModal = ref(false);
const selectedAudit = ref(null);

const currentAudit = computed(() => selectedAudit.value ?? {});

const actionMeta = {
  created: {
    label: 'Registro creado',
    labelShort: 'Creado',
    classes: 'bg-brand-border text-brand-charcoal border border-brand-border-strong',
  },
  updated: {
    label: 'Registro actualizado',
    labelShort: 'Actualizado',
    classes: 'bg-brand-border text-brand-charcoal border border-brand-border-strong',
  },
  deleted: {
    label: 'Registro eliminado',
    labelShort: 'Eliminado',
    classes: 'bg-brand-border text-brand-wine border border-brand-border-strong',
  },
  default: {
    label: 'Acción registrada',
    labelShort: 'Acción',
    classes: 'bg-brand-border text-brand-charcoal border border-brand-border-strong',
  },
};

const actionBadge = computed(() => actionMeta[currentAudit.value?.action] ?? actionMeta.default);

function openDetail(audit) {
  selectedAudit.value = audit;
  showDetailModal.value = true;
}

function closeDetail() {
  showDetailModal.value = false;
  selectedAudit.value = null;
}

function prettyJson(value) {
  if (!value || Object.keys(value).length === 0) {
    return 'Sin registros';
  }
  try {
    return JSON.stringify(value, null, 2);
  } catch (e) {
    return String(value);
  }
}

function hasAuditValues(value) {
  return value && typeof value === 'object' && Object.keys(value).length > 0;
}

function formatAction(action) {
  const meta = actionMeta[action];
  if (meta) return meta.labelShort;
  if (!action) return '-';
  return action.charAt(0).toUpperCase() + action.slice(1);
}

function modelName(type) {
  if (!type) return '-';
  const parts = type.split('\\');
  return parts.pop() || '-';
}
</script>

<template>
  <AdminLayout title="Auditoría">
    <div class="space-y-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Auditoría</h1>
        <button
          @click="exportCsv"
          class="inline-flex items-center justify-center px-4 py-2 bg-brand-red hover:bg-brand-wine text-white rounded-lg transition"
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
              class="px-3 py-2 bg-brand-red hover:bg-brand-wine text-white rounded whitespace-nowrap transition"
            >
              Filtrar
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="px-3 py-2 bg-brand-gray hover:bg-gray-200 text-brand-charcoal rounded whitespace-nowrap transition"
            >
              Reset
            </button>
          </div>
        </form>
      </div>

      <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-brand-gray border-b text-gray-700">
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
              <tr v-for="a in audits.data" :key="a.id" class="border-b hover:bg-brand-gray">
                <td class="p-2">{{ a.id }}</td>
                <td class="p-2">{{ a.user?.name ?? 'Sistema' }}</td>
                <td class="p-2">{{ modelName(a.auditable_type) }}</td>
                <td class="p-2">{{ a.auditable_id }}</td>
                <td class="p-2">
                  <div class="flex items-center justify-between gap-2">
                    <span class="text-sm font-medium text-gray-700 capitalize" :title="`Acción: ${formatAction(a.action)}`">
                      {{ formatAction(a.action) }}
                    </span>
                    <button
                      type="button"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-gray text-brand-charcoal hover:bg-gray-200 transition"
                      @click="openDetail(a)"
                      title="Ver detalles"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
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
      <Modal :show="showDetailModal" @close="closeDetail">
        <div class="w-full max-w-3xl">
          <div class="relative overflow-hidden rounded-2xl border border-brand-border-strong bg-white shadow-2xl">
            <div class="bg-brand-border px-6 py-5 text-brand-charcoal border-b border-brand-border-strong">
              <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-tint text-brand-red">
                  <ShieldCheckIcon class="h-6 w-6" />
                </div>
                <div class="flex-1">
                  <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold">Detalle de auditoría</h2>
                    <span
                      :class="[
                        'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold tracking-wide uppercase',
                        actionBadge.classes,
                      ]"
                    >
                      {{ actionBadge.label }}
                    </span>
                  </div>
                  <p class="mt-1 text-sm text-brand-muted">
                    Registro #{{ currentAudit.id }} • {{ formatDate(currentAudit.created_at) }}
                  </p>
                </div>
              </div>
            </div>

            <div class="space-y-6 px-6 py-6">
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div class="rounded-xl border border-brand-border-strong bg-brand-border p-4 shadow-sm text-brand-charcoal">
                  <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Usuario</p>
                  <p class="mt-1 font-medium">{{ currentAudit.user?.name ?? 'Sistema' }}</p>
                </div>
                <div class="rounded-xl border border-brand-border-strong bg-brand-border p-4 shadow-sm text-brand-charcoal">
                  <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Modelo</p>
                  <p class="mt-1 font-medium">{{ modelName(currentAudit.auditable_type) }}</p>
                </div>
                <div class="rounded-xl border border-brand-border-strong bg-brand-border p-4 shadow-sm text-brand-charcoal">
                  <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">ID modelo</p>
                  <p class="mt-1 font-medium">{{ currentAudit.auditable_id ?? '-' }}</p>
                </div>
                <div class="rounded-xl border border-brand-border-strong bg-brand-border p-4 shadow-sm text-brand-charcoal">
                  <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Acción</p>
                  <p class="mt-1 font-medium">{{ formatAction(currentAudit.action) }}</p>
                </div>
                <div class="rounded-xl border border-brand-border-strong bg-brand-border p-4 shadow-sm text-brand-charcoal">
                  <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">IP</p>
                  <p class="mt-1 font-medium">{{ currentAudit.ip_address ?? 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-brand-border-strong bg-brand-border p-4 shadow-sm text-brand-charcoal">
                  <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">User Agent</p>
                  <p class="mt-1 break-words font-medium">{{ currentAudit.user_agent ?? 'N/A' }}</p>
                </div>
                <div
                  v-if="currentAudit.url"
                  class="sm:col-span-2 md:col-span-3 rounded-xl border border-brand-border-strong bg-brand-border p-4 shadow-sm text-brand-charcoal"
                >
                  <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">URL</p>
                  <a
                    :href="currentAudit.url"
                    target="_blank"
                    rel="noopener"
                    class="mt-1 block break-words text-brand-red underline decoration-dashed underline-offset-4 hover:text-brand-wine"
                  >
                    {{ currentAudit.url }}
                  </a>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="overflow-hidden rounded-2xl border border-brand-border-strong bg-brand-border shadow-sm text-brand-charcoal">
                  <div class="flex items-center justify-between border-b border-brand-border-strong px-4 py-3">
                    <div class="flex items-center gap-2 text-sm font-semibold text-brand-muted">
                      <DocumentTextIcon class="h-4 w-4 text-brand-muted" />
                      <span>Valores anteriores</span>
                    </div>
                    <span class="text-xs font-medium uppercase tracking-wide text-brand-muted">Antes</span>
                  </div>
                  <pre
                    :class="[
                      'max-h-64 overflow-auto bg-white px-4 py-3 text-xs leading-5',
                      hasAuditValues(currentAudit.old_values) ? 'text-gray-800' : 'text-gray-400 italic',
                    ]"
                    v-text="prettyJson(currentAudit.old_values)"
                  ></pre>
                </div>
                <div class="overflow-hidden rounded-2xl border border-brand-border-strong bg-brand-border shadow-sm text-brand-charcoal">
                  <div class="flex items-center justify-between border-b border-brand-border-strong px-4 py-3">
                    <div class="flex items-center gap-2 text-sm font-semibold text-brand-muted">
                      <DocumentTextIcon class="h-4 w-4 text-brand-muted" />
                      <span>Valores nuevos</span>
                    </div>
                    <span class="text-xs font-medium uppercase tracking-wide text-brand-muted">Después</span>
                  </div>
                  <pre
                    :class="[
                      'max-h-64 overflow-auto bg-white px-4 py-3 text-xs leading-5',
                      hasAuditValues(currentAudit.new_values) ? 'text-gray-800' : 'text-gray-400 italic',
                    ]"
                    v-text="prettyJson(currentAudit.new_values)"
                  ></pre>
                </div>
              </div>

              <div class="flex justify-end">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg bg-brand-red px-4 py-2 text-sm font-medium text-white shadow hover:bg-brand-wine transition"
                  @click="closeDetail"
                >
                  Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>
      </Modal>
    </div>
  </AdminLayout>
</template>

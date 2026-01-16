<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  pendingApprovals: Array,
  canApprove: Boolean,
});

const pendingApprovals = computed(() => props.pendingApprovals ?? []);
let refreshTimer = null;

function formatDate(value) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

function formatStatus(status) {
  if (!status) return '-';
  return status
    .split('_')
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function pendingQuantity(solicitud) {
  const qty = Number(solicitud?.quantity) || 0;
  const approved = Number(solicitud?.approved_quantity) || 0;
  return Math.max(0, qty - approved);
}

onMounted(() => {
  if (!props.canApprove) return;
  refreshTimer = setInterval(() => {
    if (document.visibilityState !== 'visible') return;
    router.reload({ only: ['pendingApprovals', 'canApprove'], preserveState: true, preserveScroll: true });
  }, 30000);
});

onBeforeUnmount(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer);
  }
});
</script>

<template>
  <AdminLayout title="Aprobaciones">
    <div class="p-6 space-y-8">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-brand-charcoal">Solicitudes pendientes por aprobar</h1>
          <p class="text-sm text-gray-600">Revisa las solicitudes asignadas a tu etapa.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <Link
            :href="route('solicitudes.index')"
            class="px-3 py-1.5 rounded-full border border-brand-red bg-brand-red text-white text-sm hover:bg-brand-wine"
          >
            Ir a solicitudes
          </Link>
          <span class="text-xs text-gray-500">Auto-actualiza cada 30s</span>
        </div>
      </div>

      <div
        v-if="!canApprove"
        class="bg-brand-gray border-l-4 border-brand-red text-brand-charcoal p-4"
      >
        No tienes permisos para aprobar solicitudes.
      </div>

      <section v-else class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-brand-charcoal">Bandeja de aprobaciones</h2>
          <span class="text-sm text-gray-500">Total: {{ pendingApprovals.length }}</span>
        </div>
        <div v-if="pendingApprovals.length === 0" class="bg-brand-gray border-l-4 border-brand-red text-brand-charcoal p-4">
          No hay solicitudes pendientes por aprobar.
        </div>
        <div v-else class="p-4 overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-brand-gray text-brand-charcoal">
              <tr>
                <th class="px-3 py-2 text-left">Código</th>
                <th class="px-3 py-2 text-left">Producto</th>
                <th class="px-3 py-2 text-left">Origen</th>
                <th class="px-3 py-2 text-left">Cantidad</th>
                <th class="px-3 py-2 text-left">Pendiente</th>
                <th class="px-3 py-2 text-left">Estado</th>
                <th class="px-3 py-2 text-left">Fecha</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="solicitud in pendingApprovals" :key="solicitud.id" class="border-b">
                <td class="px-3 py-2 font-mono">{{ solicitud.code }}</td>
                <td class="px-3 py-2">{{ solicitud.product?.name ?? 'Sin producto' }}</td>
                <td class="px-3 py-2">{{ solicitud.fromGerencia?.name ?? 'Sin gerencia' }}</td>
                <td class="px-3 py-2">{{ solicitud.quantity }}</td>
                <td class="px-3 py-2 font-semibold text-brand-red">{{ pendingQuantity(solicitud) }}</td>
                <td class="px-3 py-2">{{ formatStatus(solicitud.status) }}</td>
                <td class="px-3 py-2">{{ formatDate(solicitud.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

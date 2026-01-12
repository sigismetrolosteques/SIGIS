<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  threshold: Number,
  lowStock: Array,
  overdueReturns: Array,
});

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

function available(inv) {
  const qty = Number(inv.quantity) || 0;
  const reserved = Number(inv.reserved_quantity) || 0;
  return Math.max(0, qty - reserved);
}

onMounted(() => {
  refreshTimer = setInterval(() => {
    if (document.visibilityState !== 'visible') return;
    router.reload({ only: ['lowStock', 'overdueReturns'], preserveState: true, preserveScroll: true });
  }, 30000);
});

onBeforeUnmount(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer);
  }
});
</script>

<template>
  <AdminLayout title="Alertas">
    <div class="p-6 space-y-8">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-brand-charcoal">Alertas del sistema</h1>
          <p class="text-sm text-gray-600">Revisa inventarios críticos y devoluciones vencidas.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 bg-brand-gray px-3 py-2 rounded-lg">
          <span class="text-xs text-gray-500">Auto-actualiza cada 30s</span>
        </div>
      </div>

      <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-brand-charcoal">Inventario con stock bajo</h2>
          <span class="text-sm text-gray-500">Total: {{ lowStock.length }}</span>
        </div>
        <div v-if="lowStock.length === 0" class="bg-brand-gray border-l-4 border-brand-red text-brand-charcoal p-4">
          No hay registros por debajo del umbral.
        </div>
        <div v-else class="p-4 overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-brand-gray text-brand-charcoal">
              <tr>
                <th class="px-3 py-2 text-left">Código</th>
                <th class="px-3 py-2 text-left">Producto</th>
                <th class="px-3 py-2 text-left">Ubicación</th>
                <th class="px-3 py-2 text-left">Cantidad</th>
                <th class="px-3 py-2 text-left">Reservado</th>
                <th class="px-3 py-2 text-left">Disponible</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="inv in lowStock" :key="inv.id" class="border-b">
                <td class="px-3 py-2 font-mono">{{ inv.product?.code ?? 'N/D' }}</td>
                <td class="px-3 py-2">{{ inv.product?.name ?? 'Producto eliminado' }}</td>
                <td class="px-3 py-2">{{ inv.location ?? 'Sin ubicación' }}</td>
                <td class="px-3 py-2">{{ inv.quantity }}</td>
                <td class="px-3 py-2">{{ inv.reserved_quantity }}</td>
                <td class="px-3 py-2 font-semibold text-brand-red">{{ available(inv) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-brand-charcoal">Devoluciones vencidas</h2>
          <span class="text-sm text-gray-500">Total: {{ overdueReturns.length }}</span>
        </div>
        <div v-if="overdueReturns.length === 0" class="bg-brand-gray border-l-4 border-brand-red text-brand-charcoal p-4">
          No hay devoluciones vencidas registradas.
        </div>
        <div v-else class="p-4 overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-brand-gray text-brand-charcoal">
              <tr>
                <th class="px-3 py-2 text-left">Código</th>
                <th class="px-3 py-2 text-left">Producto</th>
                <th class="px-3 py-2 text-left">Origen</th>
                <th class="px-3 py-2 text-left">Fecha devolución</th>
                <th class="px-3 py-2 text-left">Responsable</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="solicitud in overdueReturns" :key="solicitud.id" class="border-b">
                <td class="px-3 py-2 font-mono">{{ solicitud.code }}</td>
                <td class="px-3 py-2">{{ solicitud.product?.name }}</td>
                <td class="px-3 py-2">{{ solicitud.fromGerencia?.name ?? 'Sin gerencia' }}</td>
                <td class="px-3 py-2 text-brand-wine font-semibold">{{ formatDate(solicitud.return_date) }}</td>
                <td class="px-3 py-2">{{ solicitud.creator?.name ?? 'Sistema' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

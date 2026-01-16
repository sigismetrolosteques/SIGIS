<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { PencilIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
  inventory: Object,
  canManage: Boolean,
});

const tipoMaterialLabel = computed(() => {
  const map = {
    equipos: 'Equipos',
    herramientas: 'Herramientas',
    consumibles: 'Consumibles',
  };

  return map[props.inventory?.product?.tipo_material] ?? 'Sin clasificación';
});

const formattedCreatedAt = computed(() => {
  if (!props.inventory?.created_at) return '-';
  const date = new Date(props.inventory.created_at);
  if (Number.isNaN(date.getTime())) return props.inventory.created_at;
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}`;
});

</script>

<template>
  <AdminLayout :title="`Inventario ${inventory.product?.code ?? inventory.id}`">
    <div class="max-w-4xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
      <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100">
        <div>
          <h1 class="text-2xl font-bold text-brand-charcoal">Detalle del Inventario</h1>
          <p class="text-sm text-gray-600">
            Consulta la información completa del registro de inventario seleccionado.
          </p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="route('inventory.index')"
            class="px-4 py-2 bg-brand-gray text-brand-charcoal rounded-lg hover:bg-gray-200 transition"
          >
            Volver
          </Link>
          <Link
            v-if="canManage"
            :href="route('inventory.edit', inventory.id)"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-gray text-brand-charcoal rounded-lg hover:bg-gray-200 transition"
          >
            <PencilIcon class="w-4 h-4" />
            <span>Editar</span>
          </Link>
        </div>
      </div>

      <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <p class="text-sm text-gray-500 uppercase">Producto</p>
            <p class="text-lg font-semibold text-gray-900">{{ inventory.product?.name ?? 'Producto eliminado' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 uppercase">Código</p>
            <p class="font-mono text-gray-800">{{ inventory.product?.code ?? 'N/D' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 uppercase">Sub-Área</p>
            <p class="text-gray-800">{{ inventory.product?.sub_area || 'Sin sub-área registrada' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 uppercase">Tipo de Material</p>
            <p class="text-gray-800">{{ tipoMaterialLabel }}</p>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500 uppercase">Cantidad total</p>
              <p class="text-lg text-gray-900">{{ inventory.quantity }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 uppercase">Reservado</p>
              <p class="text-lg text-gray-900">{{ inventory.reserved_quantity }}</p>
            </div>
          </div>
          <div>
            <p class="text-sm text-gray-500 uppercase">Ubicación</p>
            <p class="text-gray-800">{{ inventory.location || 'Sin ubicación' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 uppercase">Notas</p>
            <p class="text-gray-800 whitespace-pre-line">{{ inventory.notes || 'Sin notas registradas' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 uppercase">Fecha de creación</p>
            <p class="text-gray-800">{{ formattedCreatedAt }}</p>
          </div>
        </div>

        <div class="flex items-center justify-center bg-gray-50 rounded-lg overflow-hidden h-72 shadow">
          <img
            v-if="inventory.product?.image"
            :src="`/img/${inventory.product.image}`"
            alt="Producto"
            class="h-full w-full object-cover"
          />
          <div v-else class="text-gray-500 italic">Sin imagen disponible</div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

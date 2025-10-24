<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { PencilIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  inventory: Object,
  canManage: Boolean,
});
</script>

<template>
  <AdminLayout :title="`Inventario ${inventory.product?.code ?? inventory.id}`">
    <div class="max-w-4xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
      <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Detalle del Inventario</h1>
          <p class="text-sm text-gray-600">
            Consulta la información completa del registro de inventario seleccionado.
          </p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="route('inventory.index')"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
          >
            Volver
          </Link>
          <Link
            v-if="canManage"
            :href="route('inventory.edit', inventory.id)"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
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
        </div>

        <div class="flex items-center justify-center bg-gray-50 rounded-lg p-4 min-h-[220px]">
          <img
            v-if="inventory.product?.image"
            :src="`/img/${inventory.product.image}`"
            alt="Producto"
            class="max-h-72 rounded-lg shadow"
          />
          <div v-else class="text-gray-500 italic">Sin imagen disponible</div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

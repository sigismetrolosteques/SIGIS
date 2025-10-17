<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  product: Object,
  canManage: Boolean,
});
</script>

<template>
  <AdminLayout :title="`Producto ${product.code}`">
    <div class="max-w-4xl mx-auto bg-white shadow border border-gray-200 rounded-xl overflow-hidden">
      <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Detalle del Producto</h1>
          <p class="text-sm text-gray-600">Consulta la información completa del producto registrado.</p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="route('product.index')"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
          >
            Volver
          </Link>
          <Link
            v-if="canManage"
            :href="route('product.edit', product.id)"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            Editar
          </Link>
        </div>
      </div>

      <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Código</span>
            <p class="text-lg font-mono text-gray-900">{{ product.code }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Nombre</span>
            <p class="text-lg font-semibold text-gray-900">{{ product.name }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Descripción</span>
            <p class="text-gray-700 whitespace-pre-line">{{ product.description || 'Sin descripción' }}</p>
          </div>
        </div>

        <div class="flex justify-center items-center bg-gray-50 rounded-lg p-4 min-h-[200px]">
          <img
            v-if="product.image"
            :src="`/img/${product.image}`"
            alt="Imagen del producto"
            class="max-h-72 rounded-lg shadow"
          />
          <div v-else class="text-gray-500 italic">Sin imagen disponible</div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

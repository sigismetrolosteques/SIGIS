<template>
  <AdminLayout title="Detalle del Producto">
    <div class="p-6 bg-white rounded shadow">
      <h1 class="text-2xl font-bold mb-4">{{ inventory.product.name }}</h1>
      <img v-if="inventory.product.image" :src="`/img/${inventory.product.image}`"  class="w-48 h-48 object-cover rounded mb-4" />
      <p><strong>Código:</strong> {{ inventory.product.id }}</p>
      <p><strong>Cantidad:</strong> {{ inventory.quantity }}</p>
      <p><strong>Ubicación:</strong> {{ inventory.location }}</p>
      <p><strong>Notas:</strong> {{ inventory.notes }}</p>
    </div>
    <div class="mt-6">
  <Link
    :href="route('inventory.index')"
    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-400 inline-block"
  >
    Volver
  </Link>
</div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { defineProps } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  inventory: Object
});
</script>

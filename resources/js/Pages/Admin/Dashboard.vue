<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
</script>

<template>
   <AdminLayout>
  <div>
    <h1 class="text-2xl font-bold mb-6">Panel de Administración</h1>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <p>Bienvenido al panel de administración</p>
      <!-- Contenido del dashboard -->
    </div>
  </div>
  </AdminLayout>
</template>
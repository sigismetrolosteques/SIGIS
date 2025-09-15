<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Form from './Form.vue';
const props = defineProps({ roles: Array });
</script>

<template>
  <AdminLayout title="Crear usuario">
    <Form :roles="roles" submit-label="Crear" />
  </AdminLayout>
</template>
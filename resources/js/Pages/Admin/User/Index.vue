<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
  users: Object,
  can: Object,
  is_super_admin: Boolean
});

const successMessage = ref(null);
const page = usePage();

const form = useForm({});
function destroyUser(id) {
  if (confirm('¿Eliminar este usuario?')) {
    form.delete(route('user.destroy', { user: id }), {
      preserveScroll: true,
      onSuccess: () => {
        successMessage.value = 'Usuario eliminado correctamente';
        // Recargar la página después de eliminar
        window.location.reload();
      },
      onError: (errors) => {
        console.error('Error deleting user:', errors);
      }
    });
  }
}

onMounted(() => {
  successMessage.value = page.props.flash?.success || null;
});
</script>

<template>
  <AdminLayout title="Usuarios">
    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
      {{ successMessage }}
    </div>
    
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">
        {{ is_super_admin ? 'Gestión de Usuarios' : 'Mi Perfil' }}
      </h1>
      <Link 
        v-if="can.create"
        :href="route('user.create')" 
        class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
      >
        ➕ Nuevo Usuario
      </Link>
    </div>

    <div v-if="users.data.length === 0" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
      <p>No se encontraron usuarios.</p>
    </div>

    <div v-else class="bg-white shadow rounded-lg border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 border-b border-gray-300">
          <tr>
            <th class="py-3 px-4 text-center font-semibold text-gray-700">ID</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Nombre</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Email</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Rol</th>
            <th v-if="can.edit || can.delete" class="py-3 px-4 w-32 font-semibold text-gray-700 text-center">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="u in users.data" :key="u.id" class="border-b border-gray-200 hover:bg-gray-50">
            <td class="py-3 px-4 text-center">{{ u.id }}</td>
            <td class="py-3 px-4 text-center">{{ u.name }}</td>
            <td class="py-3 px-4 text-center">{{ u.email }}</td>
            <td class="py-3 px-4 text-center">{{ u.roles.map(r => r.name).join(', ') }}</td>
            <td v-if="can.edit || can.delete" class="py-3 px-4 text-center">
              <Link 
                :href="route('user.edit', u.id)" 
                class="text-blue-600 hover:underline mr-3"
              >
                Editar
              </Link>
              <button 
                v-if="can.delete && $page.props.auth.user.id !== u.id"
                @click="destroyUser(u.id)" 
                class="text-red-600 hover:underline"
              >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :links="users.links" class="mt-6" />
  </AdminLayout>
</template>
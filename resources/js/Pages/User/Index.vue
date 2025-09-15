<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue'; // Asegúrate de que la ruta sea correcta

const props = defineProps({
  users: Object,
  can: Object,
  is_super_admin: Boolean
});

const successMessage = ref(null);
const showDeleteModal = ref(false);
const userToDelete = ref(null);
const page = usePage();

const form = useForm({});

function confirmDelete(userId) {
  userToDelete.value = userId;
  showDeleteModal.value = true;
}

function destroyUser() {
  if (!userToDelete.value) return;

  const url = route('user.destroy', userToDelete.value);
  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Usuario eliminado correctamente';
      showDeleteModal.value = false;
    },
    onError: (errors) => {
      console.error('Error deleting user:', errors);
      showDeleteModal.value = false;
    }
  });
}

onMounted(() => {
  successMessage.value = page.props.flash?.success || null;
});
</script>

<template>
  <AdminLayout title="Usuarios">

    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          ¿Estás seguro que deseas eliminar este usuario?
        </h2>

        <div class="mt-6 flex justify-end space-x-4">
          <button @click="showDeleteModal = false"
            class="px-4 py-2 text-sm text-white bg-gray-500 hover:text-white hove:bg-gray-400 rounded-md border border-gray-300">
            Cancelar
          </button>

          <button @click="destroyUser" class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-md">
            Eliminar
          </button>
        </div>
      </div>
    </Modal>

    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
      {{ successMessage }}
    </div>
    <div v-if="$page.props.errors && $page.props.errors.error" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
      {{ $page.props.errors.error }}
    </div>

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">
        {{ is_super_admin ? 'Gestión de Usuarios' : 'Mi Perfil' }}
      </h1>
      <Link v-if="can.create" :href="route('user.create')"
        class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
       Nuevo Usuario
      </Link>
    </div>

    <div v-if="users.data.length === 0" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
      <p>No se encontraron usuarios.</p>
    </div>

    <div v-else class="bg-white shadow rounded-lg border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 border-b border-gray-300">
          <tr>
            <!--th class="py-3 px-4 text-center font-semibold text-gray-700">ID</th-->
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Nombre</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Email</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Rol</th>
            <th v-if="can.edit || can.delete" class="py-3 px-4 w-32 font-semibold text-gray-700 text-center">Acciones
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="u in users.data" :key="u.id" class="border-b border-gray-200 hover:bg-gray-50">
            <!--td class="py-3 px-4 text-center">{{ u.id }}</td-->
            <td class="py-3 px-4 text-center">{{ u.name }}</td>
            <td class="py-3 px-4 text-center">{{ u.email }}</td>
           <td class="py-3 px-4 text-center">
  {{Array.isArray(u.roles) ? 
    u.roles.map(r => {
      if (r.name === 'super_admin') return 'Administrador';
      if (r.name === 'admin') return 'Gestor';
      return r.name;
    }).join(', ') : ''}}
</td>
<td v-if="can.edit || can.delete" class="py-3 px-4">
  <div class="flex justify-center items-center gap-3">
    <Link 
      :href="route('user.edit', u.id)" 
      class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-all duration-200 shadow-md hover:shadow-lg"
    >
      Editar
    </Link>
    <button 
      v-if="can.delete && $page.props.auth.user.id !== u.id" 
      @click.prevent="confirmDelete(u.id)"
      type="button" 
      class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-all duration-200 shadow-md hover:shadow-lg"
    >
      Eliminar
    </button>
  </div>
</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :links="users.links" class="mt-6" />
  </AdminLayout>
</template>
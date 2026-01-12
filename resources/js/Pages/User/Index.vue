<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue'; // Asegúrate de que la ruta sea correcta
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

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
            class="px-4 py-2 text-sm text-brand-charcoal bg-brand-gray hover:bg-gray-200 rounded-md border border-gray-300">
            Cancelar
          </button>

          <button @click="destroyUser" class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white bg-brand-wine hover:bg-brand-red rounded-md">
            <TrashIcon class="w-4 h-4" />
            <span>Eliminar</span>
          </button>
        </div>
      </div>
    </Modal>

    <div v-if="successMessage" class="mb-4 p-4 bg-brand-gray border border-brand-red/40 text-brand-charcoal rounded">
      {{ successMessage }}
    </div>
    <div v-if="$page.props.errors && $page.props.errors.error" class="mb-4 p-4 bg-brand-tint border border-brand-wine text-brand-wine rounded">
      {{ $page.props.errors.error }}
    </div>

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">
        {{ is_super_admin ? 'Gestión de Usuarios' : 'Mi Perfil' }}
      </h1>
      <Link v-if="can.create" :href="route('user.create')"
        class="inline-block bg-brand-red text-white px-4 py-2 rounded hover:bg-brand-wine transition">
      Nuevo Usuario
      </Link>
    </div>

    <div v-if="users.data.length === 0" class="bg-brand-gray border-l-4 border-brand-red text-brand-charcoal p-4 mb-4">
      <p>No se encontraron usuarios.</p>
    </div>

    <div v-else class="bg-white shadow rounded-lg border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-brand-gray border-b border-gray-300">
          <tr>
            <!--th class="py-3 px-4 text-center font-semibold text-gray-700">ID</th-->
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Nombre</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Email</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Coordinación</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-700">Rol</th>
            <th v-if="can.edit || can.delete" class="py-3 px-4 w-32 font-semibold text-gray-700 text-center">Acciones
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="u in users.data" :key="u.id" class="border-b border-gray-200 hover:bg-brand-gray">
            <!--td class="py-3 px-4 text-center">{{ u.id }}</td-->
            <td class="py-3 px-4 text-center">{{ u.name }}</td>
            <td class="py-3 px-4 text-center">{{ u.email }}</td>
            <td class="py-3 px-4 text-center">
              {{ u.gerencia ? u.gerencia.name : 'Sin gerencia' }}
            </td>

            <td class="py-3 px-4 text-center">
              {{Array.isArray(u.roles) ?
                u.roles.map(r => {
                  if (r.name === 'super_admin') return 'Super Administrador';
                  if (r.name === 'admin') return 'Gestor';
                  if (r.name === 'viewer') return 'Empleado';
                  return r.name;
                }).join(', ') : ''}}
            </td>
            <td v-if="can.edit || can.delete" class="py-3 px-4">
              <div class="flex justify-center items-center gap-3">
                <Link
                  :href="route('user.edit', u.id)"
                  class="inline-flex items-center justify-center bg-brand-gray text-brand-charcoal w-10 h-10 rounded-full hover:bg-gray-200 transition-all duration-200 shadow-md hover:shadow-lg"
                  title="Editar"
                >
                  <PencilIcon class="w-4 h-4" />
                </Link>
                <button
                  v-if="can.delete && $page.props.auth.user.id !== u.id"
                  @click.prevent="confirmDelete(u.id)"
                  type="button"
                  class="inline-flex items-center justify-center bg-brand-wine text-white w-10 h-10 rounded-full hover:bg-brand-red transition-all duration-200 shadow-md hover:shadow-lg"
                  title="Eliminar"
                >
                  <TrashIcon class="w-4 h-4" />
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

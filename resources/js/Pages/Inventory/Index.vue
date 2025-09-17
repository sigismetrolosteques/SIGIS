<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  inventories: Object,
  flash: Object
});

const page = usePage();
const form = useForm({});
const successMessage = ref(page.props.flash?.success || null);

const showDeleteModal = ref(false);
const inventoryToDelete = ref(null);

function confirmDelete(id) {
  inventoryToDelete.value = id;
  showDeleteModal.value = true;
}

function destroyInventory() {
  if (!inventoryToDelete.value) return;

  form.post(route('inventory.destroy', inventoryToDelete.value), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Registro eliminado correctamente';
      showDeleteModal.value = false;
      inventoryToDelete.value = null;
    },
    onError: (errors) => {
      console.error(errors);
      showDeleteModal.value = false;
    }
  });
}
</script>

<template>
  <AdminLayout title="Inventario">
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          ¿Estás seguro que deseas eliminar este registro de inventario?
        </h2>
        <div class="mt-6 flex justify-end space-x-4">
          <button @click="showDeleteModal = false" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-400">
            Cancelar
          </button>
          <button @click="destroyInventory" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
            Eliminar
          </button>
        </div>
      </div>
    </Modal>

    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
      {{ successMessage }}
    </div>

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Gestión de Inventario</h1>
      <Link :href="route('inventory.create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Nuevo Registro
      </Link>
    </div>

    <div v-if="inventories.data.length === 0" class="bg-yellow-100 p-4 rounded">
      No hay registros de inventario.
    </div>

    <div v-else class="bg-white shadow rounded border overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 border-b">
          <tr>
            <th class="py-3 px-4 text-left">Producto</th>
            <th class="py-3 px-4 text-left">Cantidad</th>
            <th class="py-3 px-4 text-left">Ubicación</th>
            <th class="py-3 px-4 text-left">Notas</th>
            <th class="py-3 px-4 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="inv in inventories.data" :key="inv.id" class="border-b">
            <td class="py-3 px-4">{{ inv.product.name }}</td>
            <td class="py-3 px-4">{{ inv.quantity }}</td>
            <td class="py-3 px-4">{{ inv.location }}</td>
            <td class="py-3 px-4">{{ inv.notes }}</td>
            <td class="py-3 px-4 text-center flex justify-center gap-2">
              <Link :href="route('inventory.edit', inv.id)" class="bg-gray-700 text-white px-3 py-1 rounded hover:bg-gray-600">
                Editar
              </Link>
              <button @click.prevent="confirmDelete(inv.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :links="inventories.links" class="mt-4" />
  </AdminLayout>
</template>

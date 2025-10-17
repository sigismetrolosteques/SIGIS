<script setup>
import { ref } from 'vue';
import { useForm, Link, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  inventories: Object,
  filters: Object,
  flash: Object,
  canManage: Boolean,
});

const page = usePage();
const successMessage = ref(page.props.flash?.success || null);
const deleteModalOpen = ref(false);
const inventoryToDelete = ref(null);
const filterSearch = ref(props.filters?.search ?? '');

const form = useForm({});

function confirmDelete(id) {
  if (!props.canManage) return;
  inventoryToDelete.value = id;
  deleteModalOpen.value = true;
}

function destroyInventory() {
  if (!inventoryToDelete.value) return;

  form.post(route('inventory.destroy', inventoryToDelete.value), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Registro eliminado correctamente';
      deleteModalOpen.value = false;
      inventoryToDelete.value = null;
    },
    onError: () => {
      deleteModalOpen.value = false;
    },
  });
}

function applyFilters() {
  router.get(
    route('inventory.index'),
    { search: filterSearch.value || undefined },
    { preserveState: true, preserveScroll: true, replace: true }
  );
}

function resetFilters() {
  filterSearch.value = '';
  applyFilters();
}
</script>

<template>
  <AdminLayout title="Inventario">
    <Modal :show="deleteModalOpen" @close="deleteModalOpen = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          ¿Estás seguro que deseas eliminar este registro de inventario?
        </h2>
        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="deleteModalOpen = false"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
          >
            Cancelar
          </button>
          <button
            @click="destroyInventory"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
          >
            Eliminar
          </button>
        </div>
      </div>
    </Modal>

    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
      {{ successMessage }}
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5">
      <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row sm:items-end gap-3">
        <div class="flex-1">
          <label class="text-sm font-medium text-gray-600">Buscar</label>
          <input
            v-model="filterSearch"
            type="text"
            placeholder="Producto, código o ubicación"
            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
          />
        </div>
        <div class="flex gap-2">
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Filtrar
          </button>
          <button
            type="button"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
            @click="resetFilters"
          >
            Limpiar
          </button>
        </div>
      </form>
    </div>

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Gestión de Inventario</h1>
      <Link
        v-if="canManage"
        :href="route('inventory.create')"
        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition"
      >
        Nuevo Registro
      </Link>
    </div>

    <div
      v-if="inventories.data.length === 0"
      class="bg-yellow-100 border border-yellow-200 text-yellow-800 px-4 py-3 rounded"
    >
      No hay registros de inventario.
    </div>

    <div v-else class="bg-white shadow rounded border overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 border-b">
          <tr>
            <th class="py-3 px-4 text-left">Foto</th>
            <th class="py-3 px-4 text-left">Código</th>
            <th class="py-3 px-4 text-left">Producto</th>
            <th class="py-3 px-4 text-left">Cantidad</th>
            <th class="py-3 px-4 text-left">Reservado</th>
            <th class="py-3 px-4 text-left">Ubicación</th>
            <th class="py-3 px-4 text-left">Notas</th>
            <th class="py-3 px-4 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="inv in inventories.data" :key="inv.id" class="border-b hover:bg-gray-50 transition">
            <td class="py-3 px-4">
              <img
                v-if="inv.product?.image"
                :src="`/img/${inv.product.image}`"
                alt="Foto"
                class="w-16 h-16 object-cover rounded"
              />
            </td>
            <td class="py-3 px-4 font-mono text-sm text-gray-700">{{ inv.product?.code ?? 'N/D' }}</td>
            <td class="py-3 px-4 text-gray-800">{{ inv.product?.name ?? 'Producto eliminado' }}</td>
            <td class="py-3 px-4 text-gray-700">{{ inv.quantity }}</td>
            <td class="py-3 px-4 text-gray-700">{{ inv.reserved_quantity }}</td>
            <td class="py-3 px-4 text-gray-700">{{ inv.location || 'Sin ubicación' }}</td>
            <td class="py-3 px-4 text-gray-700">{{ inv.notes || 'Sin notas' }}</td>
            <td class="py-3 px-4">
              <div class="flex items-center justify-center gap-2">
                <Link
                  :href="route('inventory.show', inv.id)"
                  class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 transition"
                >
                  Ver más
                </Link>
                <template v-if="canManage">
                  <Link
                    :href="route('inventory.edit', inv.id)"
                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-md bg-gray-700 text-white hover:bg-gray-600 transition"
                  >
                    Editar
                  </Link>
                  <button
                    @click.prevent="confirmDelete(inv.id)"
                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-md bg-red-500 text-white hover:bg-red-600 transition"
                  >
                    Eliminar
                  </button>
                </template>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :links="inventories.links" class="mt-4" />
  </AdminLayout>
</template>

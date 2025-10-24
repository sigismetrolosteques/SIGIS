<script setup>
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted } from 'vue';
import { EyeIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  products: Object,
  flash: Object,
  filters: Object,
  canManage: Boolean,
});

const page = usePage();
const successMessage = ref(null);
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const search = ref(props.filters?.search ?? '');

const form = useForm({});

function confirmDelete(productId) {
  productToDelete.value = productId;
  showDeleteModal.value = true;
}

function destroyProduct() {
  if (!productToDelete.value) return;

  form.post(route('product.destroy', productToDelete.value), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Producto eliminado correctamente';
      showDeleteModal.value = false;
    },
    onError: (errors) => {
      console.error('Error deleting product:', errors);
      showDeleteModal.value = false;
    },
  });
}

onMounted(() => {
  successMessage.value = page.props.flash?.success || null;
});

function applyFilters() {
  router.get(
    route('product.index'),
    { search: search.value || undefined },
    { preserveState: true, preserveScroll: true, replace: true }
  );
}

function resetFilters() {
  search.value = '';
  applyFilters();
}
</script>

<template>
  <AdminLayout title="Productos">

    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          ¿Estás seguro que deseas eliminar este producto?
        </h2>
        <div class="mt-6 flex justify-end space-x-4">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-sm text-white bg-gray-500 hover:bg-gray-400 rounded-md border border-gray-300"
          >
            Cancelar
          </button>
          <button
            @click="destroyProduct"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-md"
          >
            <TrashIcon class="w-4 h-4" />
            <span>Eliminar</span>
          </button>
        </div>
      </div>
    </Modal>

    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
      {{ successMessage }}
    </div>

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
      <h1 class="text-2xl font-bold">Gestión de Productos</h1>
      <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <form @submit.prevent="applyFilters" class="flex items-center gap-2">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por código o nombre"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none w-64"
          />
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Buscar
          </button>
          <button
            type="button"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
            @click="resetFilters"
          >
            Limpiar
          </button>
        </form>
        <Link
          v-if="canManage"
          :href="route('product.create')"
          class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
        >
          Nuevo Producto
        </Link>
      </div>
    </div>

    <div
      v-if="products.data.length === 0"
      class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4"
    >
      No se encontraron productos.
    </div>

    <div v-else class="bg-white shadow rounded-lg border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 border-b border-gray-300">
          <tr>
            <th class="py-3 px-4 font-semibold text-gray-700 text-left">Imagen</th>
            <th class="py-3 px-4 font-semibold text-gray-700 text-left">Código</th>
            <th class="py-3 px-4 font-semibold text-gray-700 text-left">Nombre</th>
            <th class="py-3 px-4 font-semibold text-gray-700 text-left">Descripción</th>
            <th class="py-3 px-4 font-semibold text-gray-700 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="product in products.data"
            :key="product.id"
            class="border-b border-gray-200 hover:bg-gray-50"
          >
            <td class="py-3 px-4 align-middle">
              <img
                v-if="product.image"
                :src="`/img/${product.image}`"
                class="w-16 h-16 object-cover rounded-md"
              />
            </td>
            <td class="py-3 px-4 align-middle font-mono text-sm text-gray-700 whitespace-nowrap">
              {{ product.code }}
            </td>
            <td class="py-3 px-4 align-middle break-words">{{ product.name }}</td>
            <td class="py-3 px-4 align-middle break-words">
              {{ product.description }}
            </td>
            <td class="py-3 px-4 align-middle">
              <div class="flex justify-center items-center gap-3">
                <Link
                  :href="route('product.show', product.id)"
                  class="inline-flex items-center justify-center bg-blue-600 text-white w-10 h-10 rounded-full hover:bg-blue-700 transition-all duration-200 shadow-md hover:shadow-lg"
                  title="Ver detalle"
                >
                  <EyeIcon class="w-4 h-4" />
                </Link>
                <template v-if="canManage">
                  <Link
                    :href="route('product.edit', product.id)"
                    class="inline-flex items-center justify-center bg-gray-700 text-white w-10 h-10 rounded-full hover:bg-gray-600 transition-all duration-200 shadow-md hover:shadow-lg"
                    title="Editar"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </Link>
                  <button
                    @click.prevent="confirmDelete(product.id)"
                    class="inline-flex items-center justify-center bg-red-500 text-white w-10 h-10 rounded-full hover:bg-red-600 transition-all duration-200 shadow-md hover:shadow-lg"
                    title="Eliminar"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </template>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :links="products.links" class="mt-6" />
  </AdminLayout>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
  products: Object,
  flash: Object,
});

const page = usePage();
const successMessage = ref(null);
const showDeleteModal = ref(false);
const productToDelete = ref(null);

const form = useForm({});

// Confirmar eliminación
function confirmDelete(productId) {
  productToDelete.value = productId;
  showDeleteModal.value = true;
}

// Eliminar producto
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
</script>

<template>
  <AdminLayout title="Productos">

    <!-- Modal de confirmación -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          ¿Estás seguro que deseas eliminar este producto?
        </h2>
        <div class="mt-6 flex justify-end space-x-4">
          <button @click="showDeleteModal = false"
                  class="px-4 py-2 text-sm text-white bg-gray-500 hover:bg-gray-400 rounded-md border border-gray-300">
            Cancelar
          </button>
          <button @click="destroyProduct"
                  class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-md">
            Eliminar
          </button>
        </div>
      </div>
    </Modal>

    <!-- Mensaje de éxito -->
    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
      {{ successMessage }}
    </div>

    <!-- Encabezado y botón crear -->
    <div class="flex justify-between items-center mb-6" v-if="$page.props.auth.user.roles.includes('super_admin')">
      <h1 class="text-2xl font-bold">Gestión de Productos</h1>
      <Link :href="route('product.create')"
            class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
        Nuevo Producto
      </Link>
    </div>

    <!-- Listado de productos -->
    <div v-if="products.data.length === 0"
         class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
      No se encontraron productos.
    </div>

    <div v-else class="bg-white shadow rounded-lg border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 border-b border-gray-300">
          <tr>
            <th class="py-3 px-4 font-semibold text-gray-700 text-left">Imagen</th>
            <th class="py-3 px-4 font-semibold text-gray-700 text-left">Nombre</th>
            <th class="py-3 px-4 font-semibold text-gray-700 text-left">Descripción</th>
            <th class="py-3 px-4 font-semibold text-gray-700 text-center" v-if="$page.props.auth.user.roles.includes('super_admin')">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products.data" :key="product.id" class="border-b border-gray-200 hover:bg-gray-50">
            <td class="py-3 px-4">
              <img v-if="product.image" :src="`/img/${product.image}`" class="w-16 h-16 object-cover rounded-md" />
            </td>
            <td class="py-3 px-4">{{ product.name }}</td>
            <td class="py-3 px-4">{{ product.description }}</td>
            <td class="py-3 px-4 text-center flex justify-center gap-3" v-if="$page.props.auth.user.roles.includes('super_admin')">
            
              <Link :href="route('product.edit', product.id)"
                    class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition shadow-md hover:shadow-lg">
                Editar
              </Link>
              <button @click.prevent="confirmDelete(product.id)"
                      class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition shadow-md hover:shadow-lg">
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <Pagination :links="products.links" class="mt-6" />

  </AdminLayout>
</template>

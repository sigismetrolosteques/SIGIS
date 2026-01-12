<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { EyeIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  inventories: Object,
  filters: Object,
  flash: Object,
  canManage: Boolean,
  tipoMaterialOptions: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const successMessage = ref(page.props.flash?.success || null);
const deleteModalOpen = ref(false);
const inventoryToDelete = ref(null);
const filterSearch = ref(props.filters?.search ?? '');
const selectedTipoMaterial = ref(
  Array.isArray(props.filters?.tipo_material)
    ? [...props.filters.tipo_material]
    : []
);

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
    {
      search: filterSearch.value || undefined,
      tipo_material: selectedTipoMaterial.value.length ? selectedTipoMaterial.value : undefined,
    },
    { preserveState: true, preserveScroll: true, replace: true }
  );
}

function resetFilters() {
  filterSearch.value = '';
  selectedTipoMaterial.value = [];
  applyFilters();
}

const toggleTipoMaterialFilter = (value) => {
  const index = selectedTipoMaterial.value.indexOf(value);
  if (index === -1) {
    selectedTipoMaterial.value.push(value);
  } else {
    selectedTipoMaterial.value.splice(index, 1);
  }
  applyFilters();
};

const tipoMaterialLabels = computed(() => {
  return props.tipoMaterialOptions.reduce((acc, option) => {
    acc[option.value] = option.label;
    return acc;
  }, {});
});

const formatTipoMaterial = (value) => tipoMaterialLabels.value[value] ?? 'Sin clasificación';

const resetTipoMaterialFilter = () => {
  selectedTipoMaterial.value = [];
  applyFilters();
};
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
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
          >
            <TrashIcon class="w-4 h-4" />
            <span>Eliminar</span>
          </button>
        </div>
      </div>
    </Modal>

    <div v-if="successMessage" class="mb-4 p-4 bg-brand-gray border border-brand-red/40 text-brand-charcoal rounded">
      {{ successMessage }}
    </div>
    <div v-if="$page.props.errors?.inventory" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
      {{ $page.props.errors.inventory }}
    </div>

    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-6">
      <h1 class="text-2xl font-bold text-brand-charcoal">Gestión de Inventario</h1>
      <div class="flex flex-wrap gap-2">
        <Link
          :href="route('reportes.inventario')"
          class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium bg-brand-gray text-brand-charcoal rounded-md shadow-sm hover:bg-gray-200 transition"
          title="Ver reportes"
        >
          Reportes
        </Link>
        <Link
          v-if="canManage"
          :href="route('inventory.create')"
          class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium bg-brand-red text-white rounded-md shadow-sm hover:bg-brand-wine transition flex-shrink-0"
          title="Nuevo Registro"
        >
          Nuevo Inventario
        </Link>
      </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow border border-gray-200 mb-6">
      <form @submit.prevent="applyFilters" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
          <input
            v-model="filterSearch"
            type="text"
            placeholder="Buscar por código o nombre"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:outline-none w-full md:w-64"
          />
          <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-brand-wine transition">
              Buscar
            </button>
            <button
              type="button"
              class="px-4 py-2 bg-brand-gray text-brand-charcoal rounded-lg hover:bg-gray-200 transition"
              @click="resetFilters"
            >
              Limpiar
            </button>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 md:justify-end">
          <span class="text-sm font-medium text-gray-700">Filtrar por:</span>
          <button
            v-for="option in props.tipoMaterialOptions"
            :key="option.value"
            type="button"
            @click="toggleTipoMaterialFilter(option.value)"
            :class="[
              'px-3 py-1.5 rounded-full border transition text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-1',
              selectedTipoMaterial.includes(option.value)
                ? 'bg-brand-red text-white border-brand-red shadow focus:ring-brand-red/60'
                : 'bg-white text-gray-600 border-gray-300 hover:bg-brand-gray focus:ring-gray-300'
            ]"
          >
            {{ option.label }}
          </button>
          <button
            v-if="selectedTipoMaterial.length"
            type="button"
            class="text-xs text-brand-red hover:text-brand-wine underline"
            @click="resetTipoMaterialFilter"
          >
            Limpiar
          </button>
        </div>
      </form>
    </div>

    <div
      v-if="inventories.data.length === 0"
      class="bg-brand-gray border-l-4 border-brand-red text-brand-charcoal p-4 mb-4"
    >
      No hay registros de inventario.
    </div>

    <div v-else class="bg-white shadow rounded border overflow-hidden">
      <table class="w-full">
        <thead class="bg-brand-gray border-b">
          <tr>
            <th class="py-3 px-4 text-left">Foto</th>
            <th class="py-3 px-4 text-left">Código</th>
            <th class="py-3 px-4 text-left">Producto</th>
            <th class="py-3 px-4 text-left">Tipo de Material</th>
            <th class="py-3 px-4 text-left">Cantidad</th>
            <th class="py-3 px-4 text-left">Reservado</th>
            <th class="py-3 px-4 text-left">Ubicación</th>
            <th class="py-3 px-4 text-left">Notas</th>
            <th class="py-3 px-4 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="inv in inventories.data" :key="inv.id" class="border-b hover:bg-gray-50 transition">
            <td class="py-3 px-4 align-middle">
              <img
                v-if="inv.product?.image"
                :src="`/img/${inv.product.image}`"
                alt="Foto"
                class="w-16 h-16 object-cover rounded"
              />
            </td>
            <td class="py-3 px-4 align-middle font-mono text-sm text-gray-700 whitespace-nowrap">
              {{ inv.product?.code ?? 'N/D' }}
            </td>
            <td class="py-3 px-4 align-middle text-gray-800 break-words">
              {{ inv.product?.name ?? 'Producto eliminado' }}
            </td>
            <td class="py-3 px-4 align-middle text-gray-700 whitespace-nowrap">
              {{ formatTipoMaterial(inv.product?.tipo_material) }}
            </td>
            <td class="py-3 px-4 align-middle text-gray-700 whitespace-nowrap">{{ inv.quantity }}</td>
            <td class="py-3 px-4 align-middle text-gray-700 whitespace-nowrap">{{ inv.reserved_quantity }}</td>
            <td class="py-3 px-4 align-middle text-gray-700 break-words">
              {{ inv.location || 'Sin ubicación' }}
            </td>
            <td class="py-3 px-4 align-middle text-gray-700 break-words">
              {{ inv.notes || 'Sin notas' }}
            </td>
            <td class="py-3 px-4 align-middle">
              <div class="flex justify-center items-center gap-3">
                <Link
                  :href="route('inventory.show', inv.id)"
                  class="inline-flex items-center justify-center bg-brand-red text-white w-10 h-10 rounded-full hover:bg-brand-wine transition-all duration-200 shadow-md hover:shadow-lg"
                  title="Ver detalle"
                >
                  <EyeIcon class="w-4 h-4" />
                </Link>
                <template v-if="canManage">
                  <Link
                    :href="route('inventory.edit', inv.id)"
                    class="inline-flex items-center justify-center bg-brand-gray text-brand-charcoal w-10 h-10 rounded-full hover:bg-gray-200 transition-all duration-200 shadow-md hover:shadow-lg"
                    title="Editar"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </Link>
                  <button
                    @click.prevent="confirmDelete(inv.id)"
                    class="inline-flex items-center justify-center bg-brand-wine text-white w-10 h-10 rounded-full hover:bg-brand-red transition-all duration-200 shadow-md hover:shadow-lg"
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

    <Pagination :links="inventories.links" class="mt-4" />
  </AdminLayout>
</template>

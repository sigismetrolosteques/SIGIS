<script setup>
import { ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  inventory: Object,
  products: Array,
  flash: Object,
  errors: Object,
  ubicacionOptions: {
    type: Array,
    default: () => [],
  },
});

const successMessage = ref(props.flash?.success || '');
const generalError = ref('');

const parseUbicaciones = (value) => {
  if (!value) return [];
  const allowed = new Set(props.ubicacionOptions ?? []);
  return value
    .split(',')
    .map((item) => item.trim())
    .filter((item) => item && allowed.has(item));
};

const form = useForm({
  product_id: props.inventory.product_id,
  quantity: props.inventory.quantity,
  location: props.inventory.location || '',
  ubicaciones: parseUbicaciones(props.inventory.location),
  notes: props.inventory.notes || ''
});


const submit = () => {
  form.post(route('inventory.update', props.inventory.id), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Inventario actualizado correctamente';
      setTimeout(() => successMessage.value = '', 1500);
    },
    onError: () => {
      generalError.value = 'Error al actualizar inventario';
    }
  });
};

const clearError = (field) => {
  if (form.errors[field]) form.clearErrors(field);
  generalError.value = '';
};

watch(() => form.quantity, () => clearError('quantity'));
watch(() => form.product_id, () => clearError('product_id'));
watch(
  () => form.ubicaciones,
  () => {
    form.location = form.ubicaciones.length ? form.ubicaciones.join(', ') : '';
    clearError('location');
  },
  { deep: true }
);
</script>

<template>
  <AdminLayout title="Editar Inventario">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Editar Registro de Inventario</h1>

      <div v-if="successMessage" class="mb-4 p-4 bg-brand-gray border border-brand-red/40 text-brand-charcoal rounded">
        {{ successMessage }}
      </div>
      <div v-if="generalError" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
        {{ generalError }}
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Producto -->
          <div>
            <label class="text-sm font-medium">Producto</label>
            <select v-model="form.product_id"
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-brand-red"
                    :class="{ 'border-red-500': form.errors.product_id }">
              <option value="">-- Selecciona un producto --</option>
              <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <div v-if="form.errors.product_id" class="text-red-600 mt-1 text-sm">
              {{ form.errors.product_id }}
            </div>
          </div>

          <!-- Cantidad -->
          <div>
            <label class="text-sm font-medium">Cantidad</label>
            <input type="number" v-model="form.quantity"
                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-brand-red"
                   :class="{ 'border-red-500': form.errors.quantity }" />
            <div v-if="form.errors.quantity" class="text-red-600 mt-1 text-sm">
              {{ form.errors.quantity }}
            </div>
          </div>

          <!-- Ubicación -->
          <div class="md:col-span-2">
            <label class="text-sm font-medium">Ubicación</label>
            <p class="text-xs text-gray-500 mt-1">Selecciona una o varias ubicaciones disponibles.</p>
            <div class="mt-3 flex flex-wrap gap-3">
              <label
                v-for="option in props.ubicacionOptions"
                :key="option"
                class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer bg-white hover:bg-gray-50"
              >
                <input
                  type="checkbox"
                  class="h-4 w-4"
                  :value="option"
                  v-model="form.ubicaciones"
                />
                <span class="text-sm text-gray-700">{{ option }}</span>
              </label>
            </div>
            <div v-if="form.errors.location" class="text-red-600 mt-2 text-sm">
              {{ form.errors.location }}
            </div>
          </div>

          <!-- Notas -->
          <div class="md:col-span-2">
            <label class="text-sm font-medium">Notas</label>
            <textarea v-model="form.notes"
                      class="w-full border rounded px-3 py-2"
                      placeholder="Notas adicionales"></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-4">
          <Link :href="route('inventory.index')"
                class="px-4 py-2 bg-brand-gray text-brand-charcoal rounded hover:bg-gray-200">
            Cancelar
          </Link>
          <button type="submit"
                  :disabled="form.processing"
                  class="px-4 py-2 bg-brand-red text-white rounded hover:bg-brand-wine">
            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  inventory: Object,
  products: Array,
  flash: Object,
  errors: Object
});

const successMessage = ref(props.flash?.success || '');
const generalError = ref('');

const form = useForm({
  product_id: props.inventory.product_id,
  quantity: props.inventory.quantity,
  location: props.inventory.location || '',
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
</script>

<template>
  <AdminLayout title="Editar Inventario">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Editar Registro de Inventario</h1>

      <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
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
                    class="w-full border rounded px-3 py-2"
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
                   class="w-full border rounded px-3 py-2"
                   :class="{ 'border-red-500': form.errors.quantity }" />
            <div v-if="form.errors.quantity" class="text-red-600 mt-1 text-sm">
              {{ form.errors.quantity }}
            </div>
          </div>

          <!-- Ubicación -->
          <div class="md:col-span-2">
            <label class="text-sm font-medium">Ubicación</label>
            <input type="text" v-model="form.location"
                   class="w-full border rounded px-3 py-2"
                   placeholder="Ubicación del inventario" />
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
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-400">
            Cancelar
          </Link>
          <button type="submit"
                  :disabled="form.processing"
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

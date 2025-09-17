<script setup>
import { ref, watch } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  product: Object,
  flash: Object,
  errors: Object
});

const successMessage = ref(props.flash?.success || '');
const generalError = ref('');

const form = useForm({
  name: props.product.name,
  image: null, // opcional
  description: props.product.description || ''
});

const submit = () => {
  const payload = new FormData();
  payload.append('name', form.name);
  if (form.image) payload.append('image', form.image);
  payload.append('description', form.description);

  form.post(route('product.update', props.product.id), {
    data: payload,
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Producto actualizado correctamente';
      setTimeout(() => successMessage.value = '', 1500);
    },
    onError: () => {
      generalError.value = 'Hubo un error al actualizar el producto.';
    }
  });
};

const handleFileChange = (event) => {
  form.image = event.target.files[0];
  if (form.errors.image) form.clearErrors('image');
};

const clearError = (field) => {
  if (form.errors[field]) form.clearErrors(field);
  generalError.value = '';
};

watch(() => form.name, () => clearError('name'));
watch(() => form.description, () => clearError('description'));
</script>

<template>
  <AdminLayout title="Editar Producto">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Editar Producto</h1>

      <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
        {{ successMessage }}
      </div>
      <div v-if="generalError" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
        {{ generalError }}
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- Nombre -->
          <div>
            <label class="text-sm font-medium">Nombre</label>
            <input v-model="form.name" @input="clearError('name')"
              :class="{ 'border-red-500': form.errors.name }"
              class="w-full border rounded px-3 py-2"
              placeholder="Nombre del producto" />
            <div v-if="form.errors.name" class="text-red-600 mt-1 text-sm">
              {{ form.errors.name }}
            </div>
          </div>

          <!-- Imagen -->
          <div>
            <label class="text-sm font-medium">Imagen (opcional)</label>
            <input type="file" @change="handleFileChange"
              :class="{ 'border-red-500': form.errors.image }"
              class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.image" class="text-red-600 mt-1 text-sm">
              {{ form.errors.image }}
            </div>
          </div>

          <!-- Descripción -->
          <div class="md:col-span-2">
            <label class="text-sm font-medium">Descripción</label>
            <textarea v-model="form.description"
              class="w-full border rounded px-3 py-2"
              placeholder="Descripción del producto"></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-4">
          <Link :href="route('product.index')"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-400">Cancelar</Link>
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

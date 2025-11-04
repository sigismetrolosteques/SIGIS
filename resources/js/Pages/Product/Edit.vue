<script setup>
import { ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  product: Object,
  flash: Object,
  errors: Object,
  tipoMaterialOptions: {
    type: Array,
    default: () => [],
  },
  ubicacionOptions: {
    type: Array,
    default: () => [],
  },
});

const successMessage = ref(props.flash?.success || '');
const generalError = ref('');

const form = useForm({
  name: props.product.name || '',
  sub_area: props.product.sub_area || '',
  ubicaciones: Array.isArray(props.product.ubicaciones) ? props.product.ubicaciones : [],
  tipo_material: props.product.tipo_material || '',
  marcas_sugeridas: props.product.marcas_sugeridas || '',
  modelo: props.product.modelo || '',
  dimensiones: props.product.dimensiones || '',
  especificaciones_tecnicas: props.product.especificaciones_tecnicas || '',
  image: null,
  description: props.product.description || '',
});

const clearError = (field) => {
  if (form.errors[field]) {
    form.clearErrors(field);
  }
  generalError.value = '';
};

const validateForm = () => {
  let isValid = true;
  generalError.value = '';

  if (!form.name.trim()) {
    form.setError('name', 'El nombre es obligatorio');
    isValid = false;
  }

  if (!form.tipo_material) {
    form.setError('tipo_material', 'Selecciona el tipo de material');
    isValid = false;
  }

  return isValid;
};

const submit = () => {
  if (!validateForm()) {
    generalError.value = 'Por favor corrige los errores del formulario.';
    return;
  }

  form.post(route('product.update', props.product.id), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Producto actualizado correctamente';
      setTimeout(() => (successMessage.value = ''), 1500);
    },
    onError: () => {
      generalError.value = 'Hubo un error al actualizar el producto.';
    },
  });
};

const handleFileChange = (event) => {
  form.image = event.target.files[0] || null;
  clearError('image');
};

const toggleTipoMaterial = (value) => {
  form.tipo_material = form.tipo_material === value ? '' : value;
  clearError('tipo_material');
};

watch(() => form.name, () => clearError('name'));
watch(() => form.sub_area, () => clearError('sub_area'));
watch(() => form.description, () => clearError('description'));
watch(() => form.marcas_sugeridas, () => clearError('marcas_sugeridas'));
watch(() => form.modelo, () => clearError('modelo'));
watch(() => form.dimensiones, () => clearError('dimensiones'));
watch(() => form.especificaciones_tecnicas, () => clearError('especificaciones_tecnicas'));
watch(() => form.ubicaciones, () => clearError('ubicaciones'), { deep: true });
watch(() => form.tipo_material, () => clearError('tipo_material'));
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
          <div>
            <label class="text-sm font-medium">Código</label>
            <input
              :value="props.product.code"
              class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed"
              readonly
            />
          </div>

          <div>
            <label class="text-sm font-medium">Nombre del material</label>
            <input
              v-model="form.name"
              @input="clearError('name')"
              :class="{ 'border-red-500': form.errors.name }"
              class="w-full border rounded px-3 py-2"
              placeholder="Ingresa el nombre"
            />
            <div v-if="form.errors.name" class="text-red-600 mt-1 text-sm">
              {{ form.errors.name }}
            </div>
          </div>

          <div>
            <label class="text-sm font-medium">Sub-Área</label>
            <input
              v-model="form.sub_area"
              @input="clearError('sub_area')"
              :class="{ 'border-red-500': form.errors.sub_area }"
              class="w-full border rounded px-3 py-2"
              placeholder="Ejemplo: Mantenimiento"
            />
            <div v-if="form.errors.sub_area" class="text-red-600 mt-1 text-sm">
              {{ form.errors.sub_area }}
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="text-sm font-medium">Tipo de Material</label>
            <div class="mt-2 flex flex-wrap gap-3">
              <label
                v-for="option in props.tipoMaterialOptions"
                :key="option.value"
                class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer bg-white hover:bg-gray-50"
              >
                <input
                  type="checkbox"
                  class="h-4 w-4"
                  :checked="form.tipo_material === option.value"
                  @change="toggleTipoMaterial(option.value)"
                />
                <span class="text-sm text-gray-700">{{ option.label }}</span>
              </label>
            </div>
            <div v-if="form.errors.tipo_material" class="text-red-600 mt-1 text-sm">
              {{ form.errors.tipo_material }}
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="text-sm font-medium">Ubicación</label>
            <div class="mt-2 flex flex-wrap gap-3">
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
            <div v-if="form.errors.ubicaciones" class="text-red-600 mt-1 text-sm">
              {{ form.errors.ubicaciones }}
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="text-sm font-medium">Marcas sugeridas</label>
            <textarea
              v-model="form.marcas_sugeridas"
              @input="clearError('marcas_sugeridas')"
              :class="{ 'border-red-500': form.errors.marcas_sugeridas }"
              class="w-full border rounded px-3 py-2"
              placeholder="Ingresa las marcas recomendadas"
            ></textarea>
            <div v-if="form.errors.marcas_sugeridas" class="text-red-600 mt-1 text-sm">
              {{ form.errors.marcas_sugeridas }}
            </div>
          </div>

          <div>
            <label class="text-sm font-medium">Modelo</label>
            <input
              v-model="form.modelo"
              @input="clearError('modelo')"
              :class="{ 'border-red-500': form.errors.modelo }"
              class="w-full border rounded px-3 py-2"
              placeholder="Ejemplo: MT-500"
            />
            <div v-if="form.errors.modelo" class="text-red-600 mt-1 text-sm">
              {{ form.errors.modelo }}
            </div>
          </div>

          <div>
            <label class="text-sm font-medium">Dimensiones</label>
            <input
              v-model="form.dimensiones"
              @input="clearError('dimensiones')"
              :class="{ 'border-red-500': form.errors.dimensiones }"
              class="w-full border rounded px-3 py-2"
              placeholder="Ejemplo: 50cm x 30cm"
            />
            <div v-if="form.errors.dimensiones" class="text-red-600 mt-1 text-sm">
              {{ form.errors.dimensiones }}
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="text-sm font-medium">Especificaciones técnicas</label>
            <textarea
              v-model="form.especificaciones_tecnicas"
              @input="clearError('especificaciones_tecnicas')"
              :class="{ 'border-red-500': form.errors.especificaciones_tecnicas }"
              class="w-full border rounded px-3 py-2"
              placeholder="Describe las especificaciones técnicas relevantes"
            ></textarea>
            <div v-if="form.errors.especificaciones_tecnicas" class="text-red-600 mt-1 text-sm">
              {{ form.errors.especificaciones_tecnicas }}
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="text-sm font-medium">Descripción general</label>
            <textarea
              v-model="form.description"
              @input="clearError('description')"
              :class="{ 'border-red-500': form.errors.description }"
              class="w-full border rounded px-3 py-2"
              placeholder="Información adicional del producto"
            ></textarea>
            <div v-if="form.errors.description" class="text-red-600 mt-1 text-sm">
              {{ form.errors.description }}
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="text-sm font-medium">Actualizar fotografía (opcional)</label>
            <input
              type="file"
              @change="handleFileChange"
              :class="{ 'border-red-500': form.errors.image }"
              class="w-full border rounded px-3 py-2"
              accept="image/jpeg,image/png"
            />
            <p class="text-xs text-gray-500 mt-1">Formatos permitidos: JPG, PNG. Tamaño máximo 2&nbsp;MB.</p>
            <div v-if="form.errors.image" class="text-red-600 mt-1 text-sm">
              {{ form.errors.image }}
            </div>
            <div v-if="props.product.image" class="mt-3 flex items-center gap-4">
              <img
                :src="`/img/${props.product.image}`"
                alt="Imagen actual del producto"
                class="h-24 w-24 object-cover rounded-lg border"
              />
              <span class="text-sm text-gray-600">Imagen actual</span>
            </div>
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

<script setup>
import { useForm, Link } from '@inertiajs/vue3'; 
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  user: Object,
  canEditRole: Boolean,
  roles: Array,
  gerencias: Array, // 👈 lista de gerencias desde el backend
  flash: Object,
  errors: Object
});

const successMessage = ref(props.flash?.success || '');
const generalError = ref('');

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  cedula: props.user.cedula,
  role: props.user.roles[0]?.id,
  gerencia_id: props.user.gerencia_id ?? '', // 👈 agregar gerencia
});

const submit = () => {
  form.post(route('user.update', props.user.id), { // 👈 usar put
    onSuccess: () => {
      successMessage.value = '¡Usuario actualizado correctamente!';
      setTimeout(() => (successMessage.value = ''), 5000);
    },
    onError: () => {
      generalError.value = 'Por favor corrige los errores del formulario.';
    }
  });
};

const clearError = (field) => {
  if (form.errors[field]) {
    form.clearErrors(field);
  }
};
</script>

<template>
  <AdminLayout title="Editar Usuario">

    <div class="p-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-user-edit mr-2 text-blue-500"></i> Editar Usuario
      </h1>

      <!-- Mensajes -->
      <div v-if="successMessage"
        class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-check-circle mr-2"></i> {{ successMessage }}
      </div>

      <div v-if="generalError"
        class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ generalError }}
      </div>

      <!-- Formulario -->
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <!-- Nombre, Email, Cédula -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="text-sm font-medium text-gray-700">Nombre</label>
              <input v-model="form.name" @input="clearError('name')" :class="{ 'border-red-500': form.errors.name }"
                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 transition"
                placeholder="Ej: Juan Pérez" />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Email</label>
              <input v-model="form.email" type="email" @input="clearError('email')"
                :class="{ 'border-red-500': form.errors.email }"
                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 transition"
                placeholder="usuario@correo.com" />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Cédula</label>
              <input v-model="form.cedula" @input="clearError('cedula')"
                :class="{ 'border-red-500': form.errors.cedula }"
                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 transition"
                placeholder="V-12345678 o E-987654321" />
              <div v-if="form.errors.cedula" class="mt-1 text-sm text-red-600">{{ form.errors.cedula }}</div>
            </div>
          </div>

          <!-- Gerencia -->
          <div>
            <label class="text-sm font-medium text-gray-700">Coordinación</label>
            <select v-model="form.gerencia_id"
              class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 transition">
              <option value="">Seleccione una Coordinación</option>
              <option v-for="g in gerencias" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
            <div v-if="form.errors.gerencia_id" class="mt-1 text-sm text-red-600">{{ form.errors.gerencia_id }}</div>
          </div>

          <!-- Botón -->
          <div class="flex justify-end mt-8 space-x-4">
            <Link :href="route('user.index')"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-white bg-gray-500 hover:bg-gray-400">
              Cancelar
            </Link>
            <button type="submit"
              class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition flex items-center"
              :disabled="form.processing">
              <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
              {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

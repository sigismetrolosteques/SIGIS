<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
  flash: Object,
  errors: Object,
  gerencias: Array, // 👈 aquí recibimos la lista
});

const successMessage = ref(props.flash?.success || '');
const generalError = ref('');

const form = useForm({
  name: '',
  email: '',
  cedula: '',
  gerencia_id: '', // 👈 nuevo campo
});

// Validaciones personalizadas
const validateForm = () => {
  let isValid = true;
  generalError.value = '';

  if (!form.name.trim()) {
    form.setError('name', 'El nombre es obligatorio');
    isValid = false;
  } else if (form.name.length < 3) {
    form.setError('name', 'El nombre debe tener al menos 3 caracteres');
    isValid = false;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!form.email.trim()) {
    form.setError('email', 'El email es obligatorio');
    isValid = false;
  } else if (!emailRegex.test(form.email)) {
    form.setError('email', 'Ingrese un email válido');
    isValid = false;
  }

  const cedulaRegex = /^[VEve]-?\d{5,9}$/;
  if (!form.cedula.trim()) {
    form.setError('cedula', 'La cédula es obligatoria');
    isValid = false;
  } else if (!cedulaRegex.test(form.cedula.toUpperCase())) {
    form.setError('cedula', 'Formato válido: V-12345678 o E-987654321');
    isValid = false;
  }

  if (!form.gerencia_id) {
    form.setError('gerencia_id', 'Debe seleccionar una gerencia');
    isValid = false;
  }

  return isValid;
};

const submit = () => {
  if (!validateForm()) {
    generalError.value = 'Por favor corrige los errores del formulario.';
    return;
  }

  form.post(route('user.store'), {
    onSuccess: () => {
      successMessage.value = '¡Usuario creado exitosamente!';
      setTimeout(() => {
        successMessage.value = '';
        router.visit(route('user.index'));
      }, 2000);
    },
    onError: (errors) => {
      generalError.value = 'Hubo un error al crear el usuario. Verifica los datos.';
      if (errors.email && errors.email.includes('already been taken')) {
        form.setError('email', 'Este email ya está registrado');
      }
      if (errors.cedula && errors.cedula.includes('already been taken')) {
        form.setError('cedula', 'Esta cédula ya está registrada');
      }
    }
  });
};

const clearError = (field) => {
  if (form.errors[field]) {
    form.clearErrors(field);
  }
  generalError.value = '';
};

watch(() => form.name, () => clearError('name'));
watch(() => form.email, () => clearError('email'));
watch(() => form.cedula, () => clearError('cedula'));
watch(() => form.gerencia_id, () => clearError('gerencia_id'));
</script>


<template>
  <AdminLayout title="Crear Usuario">

    <div class="p-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-user-plus mr-2 text-brand-red"></i> Crear Usuario
      </h1>

      <!-- Mensajes -->
      <div v-if="successMessage"
        class="mb-4 bg-brand-gray border border-brand-red/40 text-brand-charcoal px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-check-circle mr-2"></i> {{ successMessage }}
      </div>

      <div v-if="generalError"
        class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ generalError }}
      </div>

      <!-- Formulario -->
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <!-- Campos básicos -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="text-sm font-medium text-gray-700">Nombre</label>
              <input v-model="form.name" @input="clearError('name')" :class="{ 'border-red-500': form.errors.name }"
                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-brand-red transition"
                placeholder="Ej: Juan Pérez" />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.name }}
              </div>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Email</label>
              <input v-model="form.email" @input="clearError('email')" type="email"
                :class="{ 'border-red-500': form.errors.email }"
                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-brand-red transition"
                placeholder="usuario@correo.com" />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.email }}
              </div>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Cédula</label>
              <input v-model="form.cedula" @input="clearError('cedula')"
                :class="{ 'border-red-500': form.errors.cedula }"
                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-brand-red transition"
                placeholder="V-12345678 o E-987654321" />
              <div v-if="form.errors.cedula" class="mt-1 text-sm text-red-600">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.cedula }}
              </div>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Coordinación</label>
            <select v-model="form.gerencia_id" :class="{ 'border-red-500': form.errors.gerencia_id }"
              class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-brand-red transition">
              <option value="">Seleccione una Coordinación</option>
              <option v-for="g in props.gerencias" :key="g.id" :value="g.id">
                {{ g.name }}
              </option>
            </select>
            <div v-if="form.errors.gerencia_id" class="mt-1 text-sm text-red-600">
              <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.gerencia_id }}
            </div>
          </div>
          <!-- Nota sobre la contraseña temporal -->
          <div class="bg-brand-gray border border-brand-red/30 rounded-lg p-4">
            <p class="text-brand-charcoal">
              <i class="fas fa-info-circle mr-2"></i>
              El sistema asignará automáticamente una contraseña temporal se enviara por correo al nuevo usuario.
            </p>
          </div>

          <!-- Botones de acción -->
          <div class="flex justify-end mt-8 space-x-4">
            <Link :href="route('user.index')"
              class="px-4 py-2.5 border border-gray-300 rounded-lg text-brand-charcoal bg-brand-gray hover:bg-gray-200 transition flex items-center">
            <i class="fas fa-times-circle mr-2"></i>
            Cancelar
            </Link>

            <button type="submit"
              class="px-5 py-2.5 bg-brand-red hover:bg-brand-wine text-white font-medium rounded-lg transition flex items-center"
              :disabled="form.processing">
              <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-save mr-2"></i>
              {{ form.processing ? 'Creando...' : 'Crear Usuario' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

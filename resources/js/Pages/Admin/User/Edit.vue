<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
  user: Object,
  roles: Array,
  canEditRole: Boolean,
  security_questions: Object,
  flash: Object,
  errors: Object
});

// Estado para mostrar/ocultar respuestas
const showAnswers = ref(false);
// Estado para mostrar cambios realizados
const showChangesIndicator = ref(false);
// Estado para mensajes de éxito
const successMessage = ref(props.flash?.success || '');
// Estado para errores generales
const generalError = ref('');

// Observar cambios en el formulario
watch(() => [props.user.name, props.user.email], () => {
  showChangesIndicator.value = false;
}, { immediate: true });

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  role: props.user.roles[0]?.id,
  password: '',
  password_confirmation: '',
  // Preguntas de seguridad con marcadores de posición
  security_question_1: props.security_questions.question1,
  security_answer_1: props.security_questions.answer1,
  security_question_2: props.security_questions.question2,
  security_answer_2: props.security_questions.answer2,
  security_question_3: props.security_questions.question3,
  security_answer_3: props.security_questions.answer3,
});

// Observar cambios en el formulario
watch(form, () => {
  showChangesIndicator.value = true;
}, { deep: true });

const submit = () => {
  form.post(route('user.update', props.user), {
    onSuccess: () => {
      successMessage.value = '¡Usuario actualizado correctamente!';
      showChangesIndicator.value = false;
      // Limpiar mensaje después de 5 segundos
      setTimeout(() => {
        successMessage.value = '';
      }, 5000);
    },
    onError: (errors) => {
      if (errors.general) {
        generalError.value = errors.general;
      } else {
        generalError.value = 'Por favor corrige los errores en el formulario';
      }
    }
  });
};

// Función para manejar el enfoque en campos de respuesta
const handleAnswerFocus = (field) => {
  if (form[field] === '********') {
    form[field] = '';
  }
};

// Función para manejar la pérdida de enfoque
const handleAnswerBlur = (field) => {
  if (form[field] === '') {
    form[field] = '********';
  }
};

// Función para limpiar errores
const clearError = (field) => {
  if (form.errors[field]) {
    form.clearErrors(field);
  }
};
</script>

<template>
  <AdminLayout title="Editar Usuario">
        <div class="p-1 bg-gradient-to-r from-red-500 to-gray-600 rounded-lg"></div>
        
        <div class="p-6">
          <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-user-edit mr-2 text-brand-red"></i>
              Editar Usuario
            </h1>
            
            <!-- Indicador de cambios pendientes -->
                <!--button 
                  type="button" 
                  class="px-5 py-2.5 bg-gray-600 hover:bg-red-500/90 text-white font-medium rounded-lg transition flex items-center disabled:opacity-75 disabled:cursor-not-allowed"
                  href="route('user.index')"
                >
                  Regresar
                </button-->
                
          </div>
          
          <!-- Mensajes de éxito -->
          <div v-if="successMessage" class="mb-6 bg-brand-gray border border-brand-red/30 text-brand-charcoal px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2 text-lg"></i>
            <span>{{ successMessage }}</span>
            <button @click="successMessage = ''" class="ml-auto text-brand-charcoal hover:text-brand-wine">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <!-- Mensajes de error generales -->
          <div v-if="generalError" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-exclamation-triangle mr-2 text-lg"></i>
            <span>{{ generalError }}</span>
            <button @click="generalError = ''" class="ml-auto text-red-800 hover:text-red-900">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <form @submit.prevent="submit">
            <div class="space-y-6">
              <!-- Información básica -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Campo Nombre -->
                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-700">Nombre</label>
                  <input 
                    v-model="form.name" 
                    @input="clearError('name')"
                    :class="{'border-red-500': form.errors.name}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition"
                    placeholder="Ej: Juan Pérez"
                  >
                  <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.name }}
                  </div>
                </div>
                
                <!-- Campo Email -->
                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-700">Email</label>
                  <input 
                    v-model="form.email" 
                    type="email" 
                    @input="clearError('email')"
                    :class="{'border-red-500': form.errors.email}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition"
                    placeholder="usuario@ejemplo.com"
                  >
                  <div v-if="form.errors.email" class="mt-1 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.email }}
                  </div>
                </div>
              </div>
              
              <!-- Rol -->
              <div v-if="canEditRole">
                <label class="block mb-2 text-sm font-medium text-gray-700">Rol</label>
                <select 
                  v-model="form.role" 
                  @change="clearError('role')"
                  :class="{'border-red-500': form.errors.role}"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition"
                >
                  <option v-for="role in roles" :key="role.id" :value="role.id">
                    {{ role.name }}
                  </option>
                </select>
                <div v-if="form.errors.role" class="mt-1 text-sm text-red-600 flex items-center">
                  <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.role }}
                </div>
              </div>
              
              <!-- Contraseña -->
              <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Cambiar Contraseña</h3>
                
                <div class="space-y-4">
                  <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                      Contraseña <span class="text-gray-500 font-normal">(dejar en blanco para no cambiar)</span>
                    </label>
                    <div class="relative">
                      <input 
                        v-model="form.password" 
                        type="password"
                        @input="clearError('password')"
                        :class="{'border-red-500': form.errors.password}"
                        class="w-[49%] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition"
                        placeholder="Nueva contraseña"
                      >
                    </div>
                    <div v-if="form.errors.password" class="mt-1 text-sm text-red-600 flex items-center">
                      <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.password }}
                    </div>
                  </div>
                  
                  <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                    <div class="relative">
                      <input 
                        v-model="form.password_confirmation" 
                        type="password"
                        :class="{'border-red-500': form.errors.password_confirmation}"
                        class="w-[49%] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition"
                        placeholder="Repite la contraseña"
                      >
                    </div>
                    <div v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation" 
                         class="mt-1 text-sm text-red-600 flex items-center">
                      <i class="fas fa-exclamation-circle mr-1"></i> Las contraseñas no coinciden
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Preguntas de Seguridad -->
              <div class="border-t border-gray-200 pt-6">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-lg font-medium text-gray-900">Preguntas de Seguridad</h3>
                  
                </div>
                
                <p class="text-sm text-gray-500 mb-4">
                  <i class="fas fa-info-circle mr-1"></i>
                  Las respuestas se muestran como "********" por seguridad. 
                  Para cambiarlas, edita el campo correspondiente.
                </p>
                
                <div class="space-y-6">
                  <!-- Pregunta 1 -->
                  <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Pregunta 1</label>
                    <input 
                      v-model="form.security_question_1" 
                      @input="clearError('security_question_1')"
                      :class="{'border-red-500': form.errors.security_question_1}"
                      class="w-[49%] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition mb-2"
                      placeholder="Ej: ¿Cuál es el nombre de tu mascota?"
                    >
                    <div class="relative">
                      <input 
                        v-model="form.security_answer_1" 
                        :type="showAnswers ? 'text' : 'password'"
                        @input="clearError('security_answer_1')"
                        :class="{'border-red-500': form.errors.security_answer_1}"
                        class="w-[49%] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition"
                        placeholder="Respuesta"
                        @focus="handleAnswerFocus('security_answer_1')"
                        @blur="handleAnswerBlur('security_answer_1')"
                      >
                      <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <i class="fas fa-key text-gray-400"></i>
                      </div>
                    </div>
                    <div v-if="form.errors.security_question_1" class="mt-1 text-sm text-red-600 flex items-center">
                      <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.security_question_1 }}
                    </div>
                    <div v-if="form.errors.security_answer_1" class="mt-1 text-sm text-red-600 flex items-center">
                      <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.security_answer_1 }}
                    </div>
                  </div>
                  
                  <!-- Pregunta 2 -->
                  <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Pregunta 2</label>
                    <input 
                      v-model="form.security_question_2" 
                      @input="clearError('security_question_2')"
                      :class="{'border-red-500': form.errors.security_question_2}"
                      class="w-[49%] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition mb-2"
                      placeholder="Ej: ¿Cuál es tu libro favorito?"
                    >
                    <div class="relative">
                      <input 
                        v-model="form.security_answer_2" 
                        :type="showAnswers ? 'text' : 'password'"
                        @input="clearError('security_answer_2')"
                        :class="{'border-red-500': form.errors.security_answer_2}"
                        class="w-[49%] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition"
                        placeholder="Respuesta"
                        @focus="handleAnswerFocus('security_answer_2')"
                        @blur="handleAnswerBlur('security_answer_2')"
                      >
                      <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <i class="fas fa-key text-gray-400"></i>
                      </div>
                    </div>
                    <div v-if="form.errors.security_question_2" class="mt-1 text-sm text-red-600 flex items-center">
                      <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.security_question_2 }}
                    </div>
                    <div v-if="form.errors.security_answer_2" class="mt-1 text-sm text-red-600 flex items-center">
                      <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.security_answer_2 }}
                    </div>
                  </div>
                  
                  <!-- Pregunta 3 -->
                  <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Pregunta 3</label>
                    <input 
                      v-model="form.security_question_3" 
                      @input="clearError('security_question_3')"
                      :class="{'border-red-500': form.errors.security_question_3}"
                      class="w-[49%] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition mb-2"
                      placeholder="Ej: ¿En qué ciudad naciste?"
                    >
                    <div class="relative">
                      <input 
                        v-model="form.security_answer_3" 
                        :type="showAnswers ? 'text' : 'password'"
                        @input="clearError('security_answer_3')"
                        :class="{'border-red-500': form.errors.security_answer_3}"
                        class="w-[49%] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red focus:border-transparent transition"
                        placeholder="Respuesta"
                        @focus="handleAnswerFocus('security_answer_3')"
                        @blur="handleAnswerBlur('security_answer_3')"
                      >
                      <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <i class="fas fa-key text-gray-400"></i>
                      </div>
                    </div>
                    <div v-if="form.errors.security_question_3" class="mt-1 text-sm text-red-600 flex items-center">
                      <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.security_question_3 }}
                    </div>
                    <div v-if="form.errors.security_answer_3" class="mt-1 text-sm text-red-600 flex items-center">
                      <i class="fas fa-exclamation-circle mr-1"></i> {{ form.errors.security_answer_3 }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-between items-center mt-8">
            
              
              <div class="flex space-x-3">
                <button 
                  type="submit" 
                  class="px-5 py-2.5 bg-brand-red hover:bg-brand-wine text-white font-medium rounded-lg transition flex items-center disabled:opacity-75 disabled:cursor-not-allowed"
                  :disabled="form.processing"
                >
                  <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                  {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                </button>
                
              </div>
            </div>
          </form>
        </div>
  </AdminLayout>
</template>

<style scoped>
.form-input {
  transition: all 0.2s ease;
}

.btn-primary {
  transition: all 0.2s ease;
}

.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

.border-red-500 {
  border-color: #ef4444;
}
</style>
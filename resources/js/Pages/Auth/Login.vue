<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { EnvelopeIcon, LockClosedIcon } from '@heroicons/vue/24/outline';
import { ExclamationCircleIcon } from '@heroicons/vue/20/solid';
import BackButton from '@/Components/BackButton.vue';

defineProps({
  canResetPassword: {
    type: Boolean,
    required: true
  },
  status: {
    type: String,
    default: null
  },
  errors: { // Añade esto para manejar errores
    type: Object,
    default: () => ({})
  }
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onSuccess: (response) => {
            // Manejo directo desde el controlador
            if (response.props.requires_setup) {
                window.location.href = route('first.login');
            }
        },
        onError: (errors) => {
            form.errors = errors;
        }
    });
};
</script>

<template>
  <div class="min-h-screen flex flex-col bg-white">
    <!-- Encabezado con fondo blanco y logo del ministerio centrado -->
    <header class="bg-white py-4 px-6 flex justify-center items-center border-b border-gray-200 h-32">
      <img 
        src="/img/CINTILLO.png" 
        alt="Ministerio del Transporte" 
        class="h-full w-auto object-contain"
      >
    </header>

    <!-- Contenido principal centrado -->
    <main class="flex-grow flex flex-col justify-center items-center bg-gradient-to-br from-red-600 via-gray-400 to-gray-500 px-4 py-12">
      <Head title="Iniciar sesión" />

      <!-- Fondo animado -->
      <div class="absolute inset-0 bg-cover bg-center opacity-30 animate-fade bg-gradient-to-br from-red-600 via-gray-800"
        :style="{ backgroundImage: `url('/img/login.jpeg')` }"></div>

      <!-- Tarjeta de login -->
      <div class="relative bg-white/80 backdrop-blur-lg border border-white/30 rounded-3xl p-8 max-w-md w-full shadow-xl">
        
        <!-- Encabezado con logo del metro -->
        <div class="text-center mb-6">
          <img 
            src="/img/Metro_Los_Teques_Logo_Boceto.svg.png" 
            alt="Logo Metro Los Teques" 
            class="mx-auto h-20 w-150"
          >
          <h2 class="text-3xl font-bold text-gray-900 mb-1">Metro Los Teques</h2>
          <h3 class="text-3xl font-bold text-gray-900 mb-3">SIGIS</h3>
          <p class="text-gray-700">Inicia sesión para continuar</p>
        </div>

      <div v-if="status" class="mb-4 p-3 bg-brand-gray border border-brand-red/40 text-brand-charcoal rounded-lg text-center">
        {{ status }}
      </div>
      <!--div v-if="Object.keys(form.errors).length > 0" class="p-4 mb-4 bg-red-100 text-red-700 rounded">
        <div v-for="error in form.errors">
          {{ error }}
        </div>
      </div-->
      <!--div v-if="form.errors.email" class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700">
        <div class="flex items-center">
          <ExclamationCircleIcon class="h-5 w-5 mr-2" />
          <span>{{ form.errors.email }}</span>
        </div>
      </div-->
      <form @submit.prevent="submit" class="space-y-5">
        <!-- Mensaje general de error -->
        <div v-if="form.hasErrors" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ExclamationCircleIcon class="h-5 w-5 text-red-500" />
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700">
                Error al iniciar sesión. Verifica tus credenciales.
              </p>
            </div>
          </div>
        </div>

        <!-- Campo Email -->
        <div class="relative">
          <EnvelopeIcon class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" />
          <input
            id="email"
            type="email"
            placeholder="Ejemplo: usuario@metro.gob.ve"
            v-model="form.email"
            :class="{ 'border-red-500': form.errors.email }"
            class="w-full pl-10 pr-4 py-3 rounded-full border-2 focus:ring-2 focus:ring-red-500"
            required
          />
          <!-- Este mensaje aparecerá debajo del input -->
          <!--p v-if="form.errors.email" class="mt-1 text-sm text-red-600 ml-2">
            {{ form.errors.email }}
          </p-->
        </div>

        <!-- Password -->
        <div class="relative">
          <LockClosedIcon class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" />
          <input id="password" type="password" placeholder="Ejemplo: P@ssw0rd123" v-model="form.password" required
            autocomplete="current-password"
            class="w-full pl-10 pr-4 py-3 rounded-full border-2 focus:ring-2 focus:ring-red-500" />
          <!--p v-if="form.errors.password" class="text-sm text-red-600 mt-1">
            {{ form.errors.password }}
          </p-->
        </div>

        <!-- Opciones -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
          </div>
          <div v-if="canResetPassword">
            <Link :href="route('password.request')" class="text-sm text-red-600 hover:underline">
            ¿Olvidaste tu contraseña?
            </Link>
          </div>
        </div>

        <!-- Botón -->
        <button type="submit"
          class="w-full py-3 rounded-full bg-gray-600 hover:bg-red-400 text-white font-semibold shadow-lg transition-colors duration-200"
          :disabled="form.processing">
          <span v-if="form.processing">Ingresando...</span>
          <span v-else>Iniciar sesión</span>
        </button>
      </form>

      <!--div class="text-center text-sm text-gray-700 mt-6">
        ¿No tienes una cuenta?
        <Link
          :href="route('register')"
          class="text-red-600 hover:underline font-medium"
        >
          Regístrate aquí
        </Link>
      </div-->

      </div>
    </main>

  </div>
</template>
<style>
@keyframes fade {

  0%,
  100% {
    opacity: 0.2;
  }

  50% {
    opacity: 0.4;
  }
}

.animate-fade {
  animation: fade 10s ease-in-out infinite;
}
</style>

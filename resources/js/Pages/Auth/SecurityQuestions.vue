<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  securityQuestions: Array,
  email: String,
  token: String,
    errors: Object
})

const form = useForm({
  email: props.email,
  token: props.token,
  answer_1: '',
  answer_2: '',
  answer_3: '',
})

const submit = () => {
  form.post(route('password.verify.security'))
}
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
      <Head title="Preguntas de Seguridad" />

        <div class="absolute inset-0 bg-cover bg-center opacity-30 animate-fade bg-gradient-to-br from-red-600 via-gray-800"
             :style="{ backgroundImage: `url('/img/login.jpeg')` }">
        </div>

        <div class="relative bg-white/80 backdrop-blur-lg border border-white/30 rounded-3xl p-8 max-w-md w-full shadow-xl">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-red-600">Verificación de Seguridad</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Por favor, responde tus preguntas de seguridad
                </p>
                <p class="mt-1 text-sm font-medium text-gray-700">
                    {{ email }}
                </p>
            </div>
            <div v-if="generalError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      {{ generalError }}
    </div>
            <form @submit.prevent="submit" class="space-y-4 mt-6">
                <!-- Campos ocultos para email y token -->
                <input type="hidden" v-model="form.email">
                <input type="hidden" v-model="form.token">
                
                <!-- Pregunta 1 -->
                <div>
                    <InputLabel :value="securityQuestions[0]" />
                    <TextInput 
                        type="text" 
                        v-model="form.answer_1" 
                        class="mt-1 block w-full" 
                        required
                        autofocus
                    />
                    <InputError :message="form.errors.answer_1" class="mt-1" />
                </div>

                <!-- Pregunta 2 -->
                <div>
                    <InputLabel :value="securityQuestions[1]" />
                    <TextInput 
                        type="text" 
                        v-model="form.answer_2" 
                        class="mt-1 block w-full" 
                        required
                    />
                    <InputError :message="form.errors.answer_2" class="mt-1" />
                </div>

                <!-- Pregunta 3 -->
                <div>
                    <InputLabel :value="securityQuestions[2]" />
                    <TextInput 
                        type="text" 
                        v-model="form.answer_3" 
                        class="mt-1 block w-full" 
                        required
                    />
                    <InputError :message="form.errors.answer_3" class="mt-1" />
                </div>

                <!-- Mensaje de error general -->
                <div v-if="form.errors.general" class="p-3 bg-red-100 text-red-700 rounded-lg">
                    {{ form.errors.general }}
                </div>

                <!-- Botón -->
                <div class="flex justify-center">
                    <button type="submit"
                            class="w-full py-3 rounded-full bg-gray-600 hover:bg-red-400 text-white font-semibold shadow-lg transition-colors duration-200"
                            :disabled="form.processing">
                        <span v-if="form.processing">Verificando...</span>
                        <span v-else>Verificar Respuestas</span>
                    </button>
                </div>
            </form>

            <!-- Enlace para volver atrás -->
            <div class="mt-6 text-center">
                <a @click.prevent="$inertia.visit(route('password.request'))" 
                   class="text-sm text-red-600 hover:text-red-800 cursor-pointer">
                    Volver a ingresar email
                </a>
            </div>
    
      </div>
    </main>

  </div>
</template>

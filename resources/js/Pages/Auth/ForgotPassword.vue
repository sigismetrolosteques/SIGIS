<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const form = useForm({
    email: '',
})

const props = defineProps({
    errors: Object
})
console.log(props.errors);
const submit = () => {
    form.post(route('password.email'), {
        onError: (errors) => {
            console.log("Errores:", errors)
        }
    })
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
     
        <Head title="Recuperar Contraseña" />
       
        <div class="absolute inset-0 bg-cover bg-center opacity-30 animate-fade bg-gradient-to-br from-red-600 via-gray-800"
            :style="{ backgroundImage: `url('/img/login.jpeg')` }">
        </div>

        <div
            class="relative bg-white/80 backdrop-blur-lg border border-white/30 rounded-3xl p-8 max-w-md w-full shadow-xl">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-red-600">¿Olvidaste tu contraseña?</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Ingresa tu correo para continuar
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4 mt-6">
                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Correo electrónico" />
                    <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" required
                        autofocus />
                    <InputError :message="form.errors.email" class="mt-1" />
                </div>
                <!-- Botón -->
                <div class="flex justify-center">
                    <button type="submit"
                        class="w-full py-3 rounded-full bg-gray-600 hover:bg-red-400 text-white font-semibold shadow-lg transition-colors duration-200"
                        :disabled="form.processing">
                        <span v-if="form.processing">Verificando...</span>
                        <span v-else>Continuar</span>
                    </button>
                </div>

            </form>
        </div>

    </main>

  </div>
</template>

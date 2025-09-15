<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import { ref, computed } from 'vue'

const props = defineProps({
  email: String,
  token: String,
  errors: Object
})

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: ''
})

const passwordFocus = ref(false)

const passwordRequirements = {
  minLength: { value: 8, valid: false },
  hasUpperCase: { value: /[A-Z]/, valid: false },
  hasNumber: { value: /[0-9]/, valid: false },
  hasSpecialChar: { value: /[*.\-$#]/, valid: false }
}

const validatePassword = (password) => {
  passwordRequirements.minLength.valid = password.length >= passwordRequirements.minLength.value
  passwordRequirements.hasUpperCase.valid = passwordRequirements.hasUpperCase.value.test(password)
  passwordRequirements.hasNumber.valid = passwordRequirements.hasNumber.value.test(password)
  passwordRequirements.hasSpecialChar.valid = passwordRequirements.hasSpecialChar.value.test(password)
  
  return (
    passwordRequirements.minLength.valid &&
    passwordRequirements.hasUpperCase.valid &&
    passwordRequirements.hasNumber.valid &&
    passwordRequirements.hasSpecialChar.valid
  )
}

const passwordsMatch = computed(() => {
  return form.password === form.password_confirmation && form.password_confirmation !== ''
})

const submit = () => {
  if (!validatePassword(form.password)) {
    return
  }
  
  form.post(route('password.update'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-red-600 via-gray-400 to-gray-500 px-4 py-12">
        <Head title="Restablecer Contraseña" />

        <div class="absolute inset-0 bg-cover bg-center opacity-30 animate-fade bg-gradient-to-br from-red-600 via-gray-800"
             :style="{ backgroundImage: `url('/img/login.jpeg')` }">
        </div>

        <div class="relative bg-white/80 backdrop-blur-lg border border-white/30 rounded-3xl p-8 max-w-md w-full shadow-xl">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-red-600">Restablecer Contraseña</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Ingresa tu nueva contraseña
                </p>
            </div>
            
            <form @submit.prevent="submit" class="space-y-4 mt-6">
                <input type="hidden" v-model="form.token" />
                
                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Correo electrónico" />
                    <TextInput 
                        id="email" 
                        type="email" 
                        v-model="form.email" 
                        class="mt-1 block w-full" 
                        required
                        readonly
                    />
                    <InputError :message="form.errors.email" class="mt-1" />
                </div>

                <!-- Contraseña -->
                <div>
                    <InputLabel for="password" value="Contraseña" />
                    <TextInput 
                        id="password" 
                        type="password" 
                        v-model="form.password" 
                        @focus="passwordFocus = true"
                        @blur="passwordFocus = false"
                        @input="validatePassword(form.password)"
                        class="mt-1 block w-full" 
                        required
                        autocomplete="new-password"
                    />
                    <InputError :message="form.errors.password" class="mt-1" />
                    
                    <!-- Password Requirements -->
                    <div v-if="passwordFocus || form.password" class="mt-2 text-xs text-gray-600">
                        <p class="font-medium mb-1">La contraseña debe contener:</p>
                        <ul class="space-y-1">
                            <li class="flex items-center" :class="passwordRequirements.minLength.valid ? 'text-green-600' : 'text-red-600'">
                                <span v-html="passwordRequirements.minLength.valid ? '&#10004;' : '&#10006;'"></span>
                                <span class="ml-1">Mínimo 8 caracteres</span>
                            </li>
                            <li class="flex items-center" :class="passwordRequirements.hasUpperCase.valid ? 'text-green-600' : 'text-red-600'">
                                <span v-html="passwordRequirements.hasUpperCase.valid ? '&#10004;' : '&#10006;'"></span>
                                <span class="ml-1">Al menos 1 letra mayúscula</span>
                            </li>
                            <li class="flex items-center" :class="passwordRequirements.hasNumber.valid ? 'text-green-600' : 'text-red-600'">
                                <span v-html="passwordRequirements.hasNumber.valid ? '&#10004;' : '&#10006;'"></span>
                                <span class="ml-1">Al menos 1 número</span>
                            </li>
                            <li class="flex items-center" :class="passwordRequirements.hasSpecialChar.valid ? 'text-green-600' : 'text-red-600'">
                                <span v-html="passwordRequirements.hasSpecialChar.valid ? '&#10004;' : '&#10006;'"></span>
                                <span class="ml-1">Al menos 1 símbolo (* . - $ #)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                    <TextInput 
                        id="password_confirmation" 
                        type="password" 
                        v-model="form.password_confirmation" 
                        class="mt-1 block w-full" 
                        required
                        autocomplete="new-password"
                    />
                    <InputError :message="form.errors.password_confirmation" class="mt-1" />
                    
                    <div v-if="form.password_confirmation" class="mt-1 text-xs" :class="passwordsMatch ? 'text-green-600' : 'text-red-600'">
                        <span v-html="passwordsMatch ? '&#10004; Las contraseñas coinciden' : '&#10006; Las contraseñas no coinciden'"></span>
                    </div>
                </div>

                <!-- Botón -->
                <div class="flex justify-center">
                    <button type="submit"
                            class="w-full py-3 rounded-full bg-gray-600 hover:bg-red-400 text-white font-semibold shadow-lg transition-colors duration-200"
                            :disabled="form.processing || !validatePassword(form.password) || !passwordsMatch"
                            :class="(form.processing || !validatePassword(form.password) || !passwordsMatch) ? 'opacity-70 cursor-not-allowed' : ''">
                        <span v-if="form.processing">Restableciendo...</span>
                        <span v-else>Restablecer Contraseña</span>
                    </button>
                </div>
            </form>
        </div>
  <br><br><br>
      <footer class="py-4 px-6 bg-white border-t border-gray-200/80 text-center text-sm text-gray-500">
        <div class="max-w-7xl mx-auto">
          <img 
            src="/img/MINISTERIO DEL TRANSPORTE.png" 
            alt="Logo Metro Los Teques" 
            class="mx-auto h-20 w-150"
          >
        </div>
      </footer>
    </div>
</template>
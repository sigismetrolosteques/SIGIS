<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Validación en tiempo real
const passwordStrength = computed(() => {
    const pass = form.password;
    if (!pass) return { score: 0, suggestions: [] };

    const suggestions = [];
    let score = 0;

    // Longitud mínima
    if (pass.length >= 8) score++;
    else suggestions.push('Mínimo 8 caracteres');

    // Contiene mayúscula
    if (/[A-Z]/.test(pass)) score++;
    else suggestions.push('Al menos 1 mayúscula');

    // Contiene número
    if (/\d/.test(pass)) score++;
    else suggestions.push('Al menos 1 número');

    // Contiene símbolo
    if (/[*.\-$#]/.test(pass)) score++;
    else suggestions.push('Al menos 1 símbolo (* . - $ #)');

    return {
        score,
        suggestions,
        isValid: score === 4 && form.password === form.password_confirmation
    };
});

const updatePassword = () => {
    if (!passwordStrength.value.isValid) {
        if (form.password !== form.password_confirmation) {
            form.setError('password_confirmation', 'Las contraseñas no coinciden');
        }
        return;
    }

    form.post(route('profile.password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) passwordInput.value?.focus();
            if (form.errors.current_password) currentPasswordInput.value?.focus();
        },
    });
};
</script>

<template>
    <section>
        <header>
            <p class="mt-1 text-sm text-gray-600">
                Asegúrate de que tu cuenta utilice una contraseña larga y aleatoria para mantenerse segura.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Contraseña Actual" />
                <TextInput 
                    id="current_password" 
                    ref="currentPasswordInput" 
                    v-model="form.current_password"
                    type="password" 
                    class="mt-1 block w-full" 
                    autocomplete="current-password" 
                />
                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password" value="Nueva Contraseña" />
                <TextInput 
                    id="password" 
                    ref="passwordInput" 
                    v-model="form.password" 
                    type="password"
                    class="mt-1 block w-full" 
                    autocomplete="new-password" 
                />
                
                <!-- Indicador de fortaleza de contraseña -->
                <div v-if="form.password" class="mt-2">
                    <div class="flex gap-1 mb-1">
                        <div 
                            v-for="i in 4" 
                            :key="i"
                            class="h-1 flex-1 rounded"
                            :class="{
                                'bg-red-500': passwordStrength.score < i,
                                'bg-green-500': passwordStrength.score >= i
                            }"
                        ></div>
                    </div>
                    
                    <p class="text-xs text-gray-600">
                        <span v-if="passwordStrength.isValid" class="text-green-600">✔ Contraseña segura</span>
                        <span v-else>
                            Requisitos: 
                            <span 
                                v-for="(suggestion, index) in passwordStrength.suggestions" 
                                :key="index"
                                class="block text-red-500"
                            >
                                {{ suggestion }}
                            </span>
                        </span>
                    </p>
                </div>
                
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                <TextInput 
                    id="password_confirmation" 
                    v-model="form.password_confirmation" 
                    type="password"
                    class="mt-1 block w-full" 
                    autocomplete="new-password" 
                />
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

                <div class="flex justify-end">
            <PrimaryButton :disabled="form.processing" class="relative">
                <span class="flex items-center justify-center">
                    <span v-if="form.processing" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Guardando...
                    </span>
                    <span v-else>
                        Guardar cambios
                    </span>
                </span>
            </PrimaryButton>
        </div>
        </form>
    </section>
</template>
<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateSecurityQuestionsForm from './Partials/UpdateSecurityQuestionsForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    securityQuestions: {
        type: Object,
        required: true,
    },
    predefinedQuestions: { // ¡Añade esta propiedad!
        type: Array,
        required: true,
    },
});

const message = ref('');
const messageType = ref(''); // 'success' o 'error'

function showMessage(type, text) {
    messageType.value = type;
    message.value = text;

    setTimeout(() => {
        message.value = '';
        messageType.value = '';
    }, 4000);
}
</script>

<template>
    <Head title="Perfil" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-brand-charcoal">
                Perfil de Usuario
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <!-- Mensaje global -->
                <div v-if="message"
                    :class="messageType === 'success' ? 'bg-brand-gray border-brand-red/40 text-brand-charcoal' : 'bg-brand-tint border-brand-wine text-brand-wine'"
                    class="mb-6 border px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold" v-if="messageType === 'success'">¡Éxito! </strong>
                    <strong class="font-bold" v-else>¡Error! </strong>
                    <span class="block sm:inline">{{ message }}</span>
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdateProfileInformationForm @message="showMessage" />
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Actualizar Contraseña</h3>
                    <UpdatePasswordForm @message="showMessage" />
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Preguntas de Seguridad</h3>
                    <UpdateSecurityQuestionsForm 
                        :security-questions="securityQuestions"
                        :predefined-questions="predefinedQuestions" 
                        @message="showMessage" 
                    />
                </div>

            </div>
        </div>
    </AdminLayout>
</template>

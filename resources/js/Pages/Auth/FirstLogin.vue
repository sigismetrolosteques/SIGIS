<script setup>
import { useForm } from '@inertiajs/vue3';
import { LockClosedIcon, CheckCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import BackButton from '@/Components/BackButton.vue';
import { ref } from 'vue';

const props = defineProps({
    email: String,
    user_id: Number
});

const showModal = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');
const modalError = ref(false);

const form = useForm({
    user_id: props.user_id,
    password: '',
    password_confirmation: '',
    security_questions: [
        { question: '¿Cuál es el nombre de tu primera mascota?', answer: '' },
        { question: '¿En qué ciudad naciste?', answer: '' },
        { question: '¿Cuál es tu comida favorita?', answer: '' }
    ]
});

const passwordRequirements = [
    { text: 'Mínimo 8 caracteres', valid: false },
    { text: 'Al menos una mayúscula', valid: false },
    { text: 'Al menos un número', valid: false }
];

const validatePassword = () => {
    passwordRequirements[0].valid = form.password.length >= 8;
    passwordRequirements[1].valid = /[A-Z]/.test(form.password);
    passwordRequirements[2].valid = /\d/.test(form.password);
};

const submit = async () => {
    try {
        const response = await axios.post(route('first.login.store'), form.data(), {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        if (response.data.success) {
            window.location.href = response.data.redirect;
        } else {
            console.error('Error del servidor:', response.data.message);
            showErrorModal('Error', getErrorMessage(response.data.message));
        }
    } catch (error) {
        console.error('Error completo:', error);

        if (error.response) {
            // Manejo específico para error 422 de confirmación de contraseña
            if (error.response.status === 422 && error.response.data.errors?.password_confirmation) {
                showErrorModal(
                    'Error de validación',
                    'Las contraseñas no coinciden. Por favor, asegúrate de que ambos campos sean iguales.'
                );
            } else {
                showErrorModal(
                    `Error ${error.response.status}`,
                    getErrorMessage(error.response.data.message || 'Error desconocido')
                );
            }
        } else if (error.request) {
            showErrorModal('Error de conexión', 'No se recibió respuesta del servidor. Por favor, verifica tu conexión a internet.');
        } else {
            showErrorModal('Error', 'Ocurrió un error al configurar la solicitud: ' + error.message);
        }
    }
};

// Función para traducir mensajes de error comunes
const getErrorMessage = (message) => {
    const errorMessages = {
        'The password field confirmation does not match.': 'Las contraseñas no coinciden.',
        'The password field confirmation does not match. (and 1 more error)': 'Las contraseñas no coinciden.',
        'The password field is required.': 'La contraseña es requerida.',
        'The password must be at least 8 characters.': 'La contraseña debe tener al menos 8 caracteres.',
        'The password must contain at least one uppercase letter.': 'La contraseña debe contener al menos una letra mayúscula.',
        'The password must contain at least one number.': 'La contraseña debe contener al menos un número.',
        // Agrega más traducciones según necesites
    };

    return errorMessages[message] || message;
};

const showErrorModal = (title, message) => {
    modalTitle.value = title;
    modalMessage.value = message;
    modalError.value = true;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-gray-100 p-4">

        <div class="w-full max-w-2xl space-y-4">

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gray-500 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <img src="/img/Metro_Los_Teques_Logo_Boceto.svg.png" alt="Logo Metro Los Teques"
                            class="mx-auto h-auto w-[130px]">
						<div>
							<h1 class="text-2xl font-bold">Configuración Inicial de Seguridad</h1>
							<p class="text-white/80">Complete su perfil de seguridad</p>
						</div>
						<div class="bg-gray-500 rounded-full p-3">
                            <LockClosedIcon class="h-6 w-6" />
                        </div>
                    </div>
                </div>
				
                <!-- Contenido -->
                <div class="p-8">
												
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800">Bienvenido, {{ email }}</h2>
                        <p class="text-gray-600">
                            Por favor establezca una nueva contraseña y configure sus preguntas de seguridad.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Contraseña -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                            <input
                                type="password"
                                v-model="form.password"
                                @input="validatePassword"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition"
                                placeholder="Ejemplo: P@ssw0rd123"
                            />
                            <div class="mt-2 space-y-1">
                                <div
                                    v-for="(req, index) in passwordRequirements"
                                    :key="index"
                                    class="flex items-center"
                                >
                                    <CheckCircleIcon v-if="req.valid" class="h-4 w-4 text-brand-red mr-2" />
                                    <ExclamationCircleIcon v-else class="h-4 w-4 text-gray-400 mr-2" />
                                    <span
                                        :class="{ 'text-gray-500': !req.valid, 'text-brand-red': req.valid }"
                                        class="text-sm"
                                    >
                                        {{ req.text }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                            <input
                                type="password"
                                v-model="form.password_confirmation"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition"
                                placeholder="Confirme la contraseña"
                            />
                        </div>

                        <!-- Preguntas de Seguridad -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">Preguntas de Seguridad</h3>
                            <p class="text-sm text-gray-500">
                                Estas preguntas ayudarán a verificar tu identidad en caso de que olvides tu contraseña.
                            </p>

                            <div
                                v-for="(question, index) in form.security_questions"
                                :key="index"
                                class="space-y-3 p-4 bg-gray-50 rounded-lg"
                            >
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Pregunta {{ index + 1 }}
                                    </label>
                                    <select
                                        v-model="question.question"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition bg-white"
                                    >
                                        <option value="¿Cuál es el nombre de tu primera mascota?">
                                            ¿Cuál es el nombre de tu primera mascota?
                                        </option>
                                        <option value="¿En qué ciudad naciste?">¿En qué ciudad naciste?</option>
                                        <option value="¿Cuál es tu comida favorita?">¿Cuál es tu comida favorita?</option>
                                        <option value="¿Cuál era el nombre de tu escuela primaria?">
                                            ¿Cuál era el nombre de tu escuela primaria?
                                        </option>
                                        <option value="¿Cuál es el nombre de tu madre?">¿Cuál es el nombre de tu madre?</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Respuesta</label>
                                    <input
                                        type="text"
                                        v-model="question.answer"
                                        required
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition"
                                        :placeholder="'Ingrese respuesta para: ' + question.question"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Botón de Envío -->
                        <div class="pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red transition disabled:opacity-75 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">Guardando configuración...</span>
                        <span v-else>Guardar Configuración de Seguridad</span>
                    </button>
                    </div>
                </form>

                <div class="mt-6 flex justify-center">
					<a @click.prevent="$inertia.visit(route('login'))" 
					   class="text-sm text-red-600 hover:text-red-800 cursor-pointer">
						Volver a inicio de sesión
					</a>
                    <!--BackButton fallbackRoute="/login" label="Volver a inicio de sesión" :force-fallback="true" /-->
                </div>
            </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 text-center">
                    <p class="text-xs text-gray-500">Al completar este formulario, aceptas nuestras políticas de seguridad.
                    </p>
                </div>
            </div>
        </div>
     <Modal :show="showModal" @close="closeModal" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <ExclamationCircleIcon 
                        v-if="modalError" 
                        class="h-12 w-12 text-red-500" 
                    />
                </div>
                <h2 class="text-xl font-bold text-center mb-2">{{ modalTitle }}</h2>
                <p class="text-gray-700 text-center mb-6">{{ modalMessage }}</p>
                <div class="flex justify-center">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        Aceptar
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

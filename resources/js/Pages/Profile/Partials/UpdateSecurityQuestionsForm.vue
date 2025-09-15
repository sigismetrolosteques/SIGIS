<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';

const props = defineProps({
    securityQuestions: {
        type: Object,
        required: true,
        default: () => ({
            question_1: '',
            question_2: '',
            question_3: ''
        })
    },
    predefinedQuestions: {
        type: Array,
        required: true,
        default: () => []
    }
});

// Estado para controlar qué preguntas son personalizadas
const isCustomQuestion = ref({
    question_1: false,
    question_2: false,
    question_3: false,
});

// Controlar si se han modificado respuestas
const answersModified = ref({
    answer_1: false,
    answer_2: false,
    answer_3: false
});

// Formulario reactivo
const form = useForm({
    question_1: props.securityQuestions.question_1,
    answer_1: '',
    question_2: props.securityQuestions.question_2,
    answer_2: '',
    question_3: props.securityQuestions.question_3,
    answer_3: '',
});

// Opciones para los selects
const questionOptions = computed(() => [
    ...props.predefinedQuestions.map(q => ({ value: q, label: q })),
    { value: 'custom', label: '✏️ Escribir pregunta personalizada' }
]);

// Verificar si las preguntas actuales son personalizadas
const checkCustomQuestions = () => {
    if (!props.securityQuestions || !props.predefinedQuestions) return;
    
    isCustomQuestion.value.question_1 = props.securityQuestions.question_1 && 
                                      !props.predefinedQuestions.includes(props.securityQuestions.question_1);
    isCustomQuestion.value.question_2 = props.securityQuestions.question_2 && 
                                      !props.predefinedQuestions.includes(props.securityQuestions.question_2);
    isCustomQuestion.value.question_3 = props.securityQuestions.question_3 && 
                                      !props.predefinedQuestions.includes(props.securityQuestions.question_3);
};

// Manejar cambio en el select
const handleQuestionChange = (field, value) => {
    const fieldName = `question_${field}`;
    isCustomQuestion.value[fieldName] = (value === 'custom');
    
    if (isCustomQuestion.value[fieldName]) {
        form[fieldName] = '';
    }
};

// Observar cambios en las respuestas
watch(() => [form.answer_1, form.answer_2, form.answer_3], ([a1, a2, a3]) => {
    answersModified.value.answer_1 = a1 !== '';
    answersModified.value.answer_2 = a2 !== '';
    answersModified.value.answer_3 = a3 !== '';
});

// Validar que si se modifica una respuesta, las otras dos también deben completarse
const validateAnswers = () => {
    const modifiedCount = Object.values(answersModified.value).filter(Boolean).length;
    
    if (modifiedCount > 0 && modifiedCount < 3) {
        form.errors.answer_1 = 'Debes actualizar las tres respuestas de seguridad';
        return false;
    }
    return true;
};

// Enviar el formulario
const submit = () => {
    if (!validateAnswers()) return;
    
    form.post(route('profile.security-questions.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('answer_1', 'answer_2', 'answer_3');
            emit('message', 'success', 'Preguntas de seguridad actualizadas');
        },
        onError: () => {
            emit('message', 'error', 'Error al actualizar las preguntas');
        },
    });
};

// Inicialización
checkCustomQuestions();

const emit = defineEmits(['message']);
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Pregunta 1 -->
        <div class="p-4 bg-gray-50 rounded-lg">
            <InputLabel for="question_1" value="Pregunta de seguridad 1" />
            
            <div class="mt-1 transition-all duration-200">
                <SelectInput
                    v-if="!isCustomQuestion.question_1"
                    v-model="form.question_1"
                    :options="questionOptions"
                    @update:modelValue="(val) => handleQuestionChange(1, val)"
                    class="w-full"
                    id="question_1"
                />
                <TextInput
                    v-else
                    v-model="form.question_1"
                    type="text"
                    class="w-full"
                    placeholder="Escribe tu pregunta personalizada"
                    required
                    id="custom_question_1"
                />
            </div>
            
            <InputLabel for="answer_1" value="Nueva respuesta 1" class="mt-4" />
            <TextInput
                id="answer_1"
                v-model="form.answer_1"
                type="text"
                class="w-full mt-1"
                autocomplete="off"
                placeholder="Dejar en blanco si no desea cambiar"
            />
            <InputError class="mt-2" :message="form.errors.answer_1" />
        </div>

        <!-- Pregunta 2 -->
        <div class="p-4 bg-gray-50 rounded-lg">
            <InputLabel for="question_2" value="Pregunta de seguridad 2" />
            
            <div class="mt-1 transition-all duration-200">
                <SelectInput
                    v-if="!isCustomQuestion.question_2"
                    v-model="form.question_2"
                    :options="questionOptions"
                    @update:modelValue="(val) => handleQuestionChange(2, val)"
                    class="w-full"
                    id="question_2"
                />
                <TextInput
                    v-else
                    v-model="form.question_2"
                    type="text"
                    class="w-full"
                    placeholder="Escribe tu pregunta personalizada"
                    required
                    id="custom_question_2"
                />
            </div>
            
            <InputLabel for="answer_2" value="Nueva respuesta 2" class="mt-4" />
            <TextInput
                id="answer_2"
                v-model="form.answer_2"
                type="text"
                class="w-full mt-1"
                autocomplete="off"
                placeholder="Dejar en blanco si no desea cambiar"
            />
        </div>

        <!-- Pregunta 3 -->
        <div class="p-4 bg-gray-50 rounded-lg">
            <InputLabel for="question_3" value="Pregunta de seguridad 3" />
            
            <div class="mt-1 transition-all duration-200">
                <SelectInput
                    v-if="!isCustomQuestion.question_3"
                    v-model="form.question_3"
                    :options="questionOptions"
                    @update:modelValue="(val) => handleQuestionChange(3, val)"
                    class="w-full"
                    id="question_3"
                />
                <TextInput
                    v-else
                    v-model="form.question_3"
                    type="text"
                    class="w-full"
                    placeholder="Escribe tu pregunta personalizada"
                    required
                    id="custom_question_3"
                />
            </div>
            
            <InputLabel for="answer_3" value="Nueva respuesta 3" class="mt-4" />
            <TextInput
                id="answer_3"
                v-model="form.answer_3"
                type="text"
                class="w-full mt-1"
                autocomplete="off"
                placeholder="Dejar en blanco si no desea cambiar"
            />
        </div>

        <div class="p-4 bg-blue-50 text-blue-800 rounded-lg text-sm">
            <p>Si actualizas una respuesta de seguridad, debes actualizar las tres por motivos de seguridad.</p>
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
</template>
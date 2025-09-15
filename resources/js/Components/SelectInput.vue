<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    options: {
        type: Array,
        required: true
    },
    id: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const handleChange = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <select
        :id="id"
        :value="modelValue"
        @change="handleChange"
        :required="required"
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
    >
        <option 
            v-for="option in options" 
            :key="option.value" 
            :value="option.value"
        >
            {{ option.label }}
        </option>
    </select>
</template>
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
        class="rounded-lg border-brand-border-strong bg-brand-white text-brand-charcoal shadow-sm w-full placeholder:text-gray-400 focus:border-brand-red focus:ring-brand-red focus:ring-2"
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

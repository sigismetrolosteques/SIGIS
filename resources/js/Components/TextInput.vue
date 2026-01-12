<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-lg border-brand-border-strong bg-brand-white text-brand-charcoal shadow-sm placeholder:text-gray-400 focus:border-brand-red focus:ring-brand-red focus:ring-2"
        v-bind="$attrs"
        v-model="model"
        ref="input"
    />
</template>

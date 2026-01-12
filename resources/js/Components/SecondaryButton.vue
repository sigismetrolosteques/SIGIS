<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-lg border border-brand-border-strong bg-gray-100 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-brand-charcoal shadow-sm transition duration-150 ease-in-out hover:bg-brand-border focus:outline-none focus:ring-2 focus:ring-brand-red focus:ring-offset-2 disabled:opacity-25"
    >
        <slot />
    </button>
</template>

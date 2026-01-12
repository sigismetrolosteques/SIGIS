<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-red text-start text-base font-medium text-brand-red bg-brand-tint focus:outline-none focus:text-brand-wine focus:bg-brand-tint focus:border-brand-wine transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-brand-muted hover:text-brand-charcoal hover:bg-brand-gray hover:border-brand-border focus:outline-none focus:text-brand-charcoal focus:bg-brand-gray focus:border-brand-border transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

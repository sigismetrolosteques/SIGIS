<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm leading-5 text-brand-charcoal transition duration-150 ease-in-out hover:bg-brand-gray focus:bg-brand-gray focus:outline-none"
    >
        <slot />
    </Link>
</template>

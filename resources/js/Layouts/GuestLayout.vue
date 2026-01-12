<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import BackButton from '@/Components/BackButton.vue';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-brand-gray pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-auto" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-brand-surface px-6 py-4 shadow-lg border border-brand-border sm:max-w-md sm:rounded-xl"
        >
            <div class="mb-4">
                <BackButton fallbackRoute="/" />
            </div>
            <slot />
        </div>
    </div>
</template>

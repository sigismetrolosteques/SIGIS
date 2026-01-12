<script setup>
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  fallbackRoute: {
    type: String,
    default: '/'
  },
  label: {
    type: String,
    default: 'Regresar'
  },
  forceFallback: {
    type: Boolean,
    default: false
  }
});

const handleBack = () => {
  if (props.forceFallback) {
    router.visit(props.fallbackRoute);
    return;
  }

  if (window.history.length > 1) {
    window.history.back();
    return;
  }

  router.visit(props.fallbackRoute);
};
</script>

<template>
  <button
    type="button"
    @click="handleBack"
    class="inline-flex items-center gap-1 text-sm font-semibold text-brand-red hover:text-brand-wine transition-colors underline underline-offset-2"
  >
    <ArrowLeftIcon class="w-4 h-4" />
    <span>{{ label }}</span>
  </button>
</template>

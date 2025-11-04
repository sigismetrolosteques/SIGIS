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
  }
});

const handleBack = () => {
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
    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-full shadow-sm hover:bg-gray-100 transition-colors"
  >
    <ArrowLeftIcon class="w-5 h-5" />
    <span>{{ label }}</span>
  </button>
</template>

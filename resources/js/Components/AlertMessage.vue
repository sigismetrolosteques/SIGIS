<script setup>
const props = defineProps({
  type: {
    type: String,
    default: 'success', // 'success' o 'error'
  },
  message: {
    type: String,
    required: true,
  }
});
</script>

<template>
  <div
    :class="[
      'border px-4 py-3 rounded relative mb-4',
      type === 'success' ? 'bg-status-approved-bg border-status-approved-brd text-status-approved-text' : '',
      type === 'error' ? 'bg-status-danger-bg border-status-danger-brd text-status-danger-text' : '',
    ]"
    role="alert"
  >
    <strong class="font-bold" v-if="type === 'success'">¡Éxito! </strong>
    <strong class="font-bold" v-else>¡Error! </strong>
    <span class="block sm:inline">{{ message }}</span>
  </div>
</template>

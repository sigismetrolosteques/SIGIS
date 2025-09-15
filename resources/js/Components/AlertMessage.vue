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
      type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : '',
      type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : '',
    ]"
    role="alert"
  >
    <strong class="font-bold" v-if="type === 'success'">¡Éxito! </strong>
    <strong class="font-bold" v-else>¡Error! </strong>
    <span class="block sm:inline">{{ message }}</span>
  </div>
</template>

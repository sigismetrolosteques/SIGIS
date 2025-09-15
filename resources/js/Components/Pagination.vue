<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
  links: Array
});
</script>

<template>
  <div v-if="links.length > 3" class="flex flex-wrap mt-4">
    <template v-for="(link, key) in links" :key="key">
      <div v-if="link.url === null" class="px-4 py-2 text-sm text-gray-400" v-html="link.label" />
      <Link
        v-else
        class="px-4 py-2 text-sm border rounded-md mx-1"
        :class="{
          'bg-gray-200 text-gray-700 font-bold': link.active,
          'text-gray-700 hover:bg-gray-100': !link.active
        }"
        :href="link.url"
        v-html="link.label"
      />
    </template>
  </div>
</template>

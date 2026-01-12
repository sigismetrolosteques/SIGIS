<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  links: {
    type: Array,
    default: () => [],
  },
});

const decodeLabel = (label) => {
  if (typeof label !== 'string') return '';

  return label
    .replace(/&laquo;/g, '«')
    .replace(/&raquo;/g, '»')
    .replace(/&nbsp;/g, ' ')
    .trim();
};

const formatLabel = (label) => {
  const decoded = decodeLabel(label);
  const lower = decoded.toLowerCase();

  if (lower.includes('previous') || lower.includes('anterior')) {
    return 'Anterior';
  }
  if (lower.includes('next') || lower.includes('siguiente')) {
    return 'Siguiente';
  }

  return decoded;
};
</script>

<template>
  <div v-if="props.links.length > 3" class="flex flex-wrap mt-4">
    <template v-for="(link, key) in props.links" :key="key">
      <div v-if="link.url === null" class="px-4 py-2 text-sm text-brand-muted">
        {{ formatLabel(link.label) }}
      </div>
      <Link
        v-else
        class="px-4 py-2 text-sm border rounded-lg mx-1 transition"
        :class="{
          'bg-brand-tint text-brand-red border-brand-border-strong font-semibold': link.active,
          'text-brand-charcoal border-brand-border hover:bg-brand-gray': !link.active
        }"
        :href="link.url"
      >
        {{ formatLabel(link.label) }}
      </Link>
    </template>
  </div>
</template>

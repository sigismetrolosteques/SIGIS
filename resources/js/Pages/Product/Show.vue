<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { PencilIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
  product: Object,
  canManage: Boolean,
});

const tipoMaterialLabel = computed(() => {
  const map = {
    equipos: 'Equipos',
    herramientas: 'Herramientas',
    consumibles: 'Consumibles',
  };

  return map[props.product?.tipo_material] ?? 'Sin clasificación';
});

const formattedCreatedAt = computed(() => {
  if (!props.product?.created_at) return '-';
  const date = new Date(props.product.created_at);
  if (Number.isNaN(date.getTime())) return props.product.created_at;
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}`;
});

function exportPdf() {
  if (!props.product) return;
  window.open(route('product.pdf', props.product.id), '_blank');
}

</script>

<template>
  <AdminLayout :title="`Producto ${product.code}`">
    <div class="max-w-4xl mx-auto bg-white shadow border border-gray-200 rounded-xl overflow-hidden">
      <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-brand-charcoal">Detalle del Producto</h1>
          <p class="text-sm text-gray-600">Consulta la información completa del producto registrado.</p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="route('product.index')"
            class="px-4 py-2 bg-brand-gray text-brand-charcoal rounded-lg hover:bg-gray-200 transition"
          >
            Volver
          </Link>
          <button
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-brand-wine transition"
            @click="exportPdf"
          >
            <DocumentArrowDownIcon class="w-4 h-4" />
            <span>Exportar ficha</span>
          </button>
          <Link
            v-if="canManage"
            :href="route('product.edit', product.id)"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-gray text-brand-charcoal rounded-lg hover:bg-gray-200 transition"
          >
            <PencilIcon class="w-4 h-4" />
            <span>Editar</span>
          </Link>
        </div>
      </div>

      <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Código</span>
            <p class="text-lg font-mono text-gray-900">{{ product.code }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Nombre</span>
            <p class="text-lg font-semibold text-gray-900">{{ product.name }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Sub-Área</span>
            <p class="text-gray-700">{{ product.sub_area || 'Sin sub-área registrada' }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Tipo de Material</span>
            <p class="text-gray-700">{{ tipoMaterialLabel }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Modelo</span>
            <p class="text-gray-700">{{ product.modelo || 'Sin modelo especificado' }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Dimensiones</span>
            <p class="text-gray-700 whitespace-pre-line">{{ product.dimensiones || 'Sin dimensiones registradas' }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Marcas sugeridas</span>
            <p class="text-gray-700 whitespace-pre-line">{{ product.marcas_sugeridas || 'Sin marcas sugeridas' }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Especificaciones técnicas</span>
            <p class="text-gray-700 whitespace-pre-line">{{ product.especificaciones_tecnicas || 'Sin especificaciones registradas' }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Descripción general</span>
            <p class="text-gray-700 whitespace-pre-line">{{ product.description || 'Sin descripción' }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Fecha de creación</span>
            <p class="text-gray-700">{{ formattedCreatedAt }}</p>
          </div>
        </div>

        <div class="flex justify-center items-center bg-gray-50 rounded-lg p-4 min-h-[200px]">
          <img
            v-if="product.image"
            :src="`/img/${product.image}`"
            alt="Imagen del producto"
            class="max-h-72 rounded-lg shadow"
          />
          <div v-else class="text-gray-500 italic">Sin imagen disponible</div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

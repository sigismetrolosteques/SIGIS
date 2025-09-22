<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  products: Array,
  gerencias: Array,
  inventories: Array, // todos los inventarios
  flash: Object,
});

const form = useForm({
  product_id: '',
  inventory_id: '',
  quantity: 1,
  to_gerencia_id: '',
  description: '',
});

const generalError = ref('');
const successMessage = ref(props.flash?.success || null);

// inventarios filtrados por producto seleccionado
const inventoriesForProduct = computed(() => {
  if (!form.product_id) return [];
  return props.inventories.filter(i => i.product_id === Number(form.product_id));
});

// lista de cantidades (1..stock disponible) para el inventario seleccionado
const quantityOptions = computed(() => {
  const inv = props.inventories.find(i => i.id === Number(form.inventory_id));
  if (!inv) return [];

  // stock disponible = total - reservado
  const available = (Number(inv.quantity) || 0) - (Number(inv.reserved_quantity) || 0);
  if (available <= 0) return [];

  const arr = [];
  for (let i = 1; i <= available; i++) {
    arr.push(i);
    if (i >= 1000) break; // seguridad
  }
  return arr;
});

// limpiar errores cuando cambian selects
watch(() => form.product_id, () => {
  form.inventory_id = '';
  form.quantity = 1;
  form.clearErrors();
  generalError.value = '';
});

watch(() => form.inventory_id, () => {
  form.quantity = 1;
  form.clearErrors();
  generalError.value = '';
});

function submit() {
  form.post(route('solicitudes.store'), {
    onError: () => {
      generalError.value = 'Corrige los errores del formulario';
    },
    onSuccess: () => {
      successMessage.value = 'Solicitud creada correctamente';
      router.visit(route('solicitudes.index'));
    }
  });
}
</script>

<template>
  <AdminLayout title="Crear Solicitud">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Crear Solicitud</h1>

      <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
        {{ successMessage }}
      </div>
      <div v-if="generalError" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
        {{ generalError }}
      </div>

      <form @submit.prevent="submit" class="space-y-6">

        <!-- Producto -->
        <div>
          <label class="text-sm font-medium">Producto</label>
          <select v-model="form.product_id"
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': form.errors.product_id }">
            <option value="">-- Selecciona un producto --</option>
            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <div v-if="form.errors.product_id" class="text-red-600 mt-1 text-sm">{{ form.errors.product_id }}</div>
        </div>

        <!-- Inventario (filtrado por producto) -->
        <div>
          <label class="text-sm font-medium">Inventario (ubicación)</label>
          <select v-model="form.inventory_id"
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': form.errors.inventory_id }">
            <option value="">-- Selecciona inventario --</option>
            <option v-for="inv in inventoriesForProduct" :key="inv.id" :value="inv.id">
              {{ inv.location ?? ('ID ' + inv.id) }} — Disponible: {{ (inv.quantity - inv.reserved_quantity) }} (de {{ inv.quantity }})
            </option>
          </select>
          <div v-if="form.errors.inventory_id" class="text-red-600 mt-1 text-sm">{{ form.errors.inventory_id }}</div>
        </div>

        <!-- Cantidad -->
        <div>
          <label class="text-sm font-medium">Cantidad</label>
          <select v-model="form.quantity"
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': form.errors.quantity }">
            <option value="">-- Selecciona cantidad --</option>
            <option v-for="q in quantityOptions" :key="q" :value="q">{{ q }}</option>
          </select>
          <div v-if="form.errors.quantity" class="text-red-600 mt-1 text-sm">{{ form.errors.quantity }}</div>
        </div>

        <!-- Gerencia destino -->
        <div>
          <label class="text-sm font-medium">Gerencia destino</label>
          <select v-model="form.to_gerencia_id" class="w-full border rounded px-3 py-2">
            <option value="">-- Selecciona gerencia --</option>
            <option v-for="g in gerencias" :key="g.id" :value="g.id">{{ g.name ?? g.nombre ?? g.id }}</option>
          </select>
          <div v-if="form.errors.to_gerencia_id" class="text-red-600 mt-1 text-sm">{{ form.errors.to_gerencia_id }}</div>
        </div>

        <!-- Descripción -->
        <div>
          <label class="text-sm font-medium">Descripción</label>
          <textarea v-model="form.description" class="w-full border rounded px-3 py-2" placeholder="Descripción (opcional)"></textarea>
        </div>

        <div class="flex justify-end gap-4">
          <Link :href="route('solicitudes.index')" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-400">Cancelar</Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            {{ form.processing ? 'Guardando...' : 'Crear Solicitud' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

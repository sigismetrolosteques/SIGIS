<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  products: Array,
  inventories: Array, // todos los inventarios
  flash: Object,
});

const activeTab = ref('inventario');

const form = useForm({
  request_type: 'inventario',
  product_id: '',
  inventory_id: null,
  quantity: 1,
  description: '',
  return_date: '',
});

const generalError = ref('');
const successMessage = ref(props.flash?.success || null);

// inventarios filtrados por producto seleccionado
const availableInventoryProductIds = computed(() => {
  const ids = new Set();
  props.inventories.forEach((inventory) => {
    const available = (Number(inventory.quantity) || 0) - (Number(inventory.reserved_quantity) || 0);
    if (available > 0) {
      ids.add(inventory.product_id);
    }
  });
  return ids;
});

const productsForInventario = computed(() =>
  props.products.filter(product => availableInventoryProductIds.value.has(product.id))
);

const displayedProducts = computed(() =>
  activeTab.value === 'inventario' ? productsForInventario.value : props.products
);

const selectedProduct = computed(() => {
  const id = Number(form.product_id);
  if (!id) return null;
  return props.products.find(product => product.id === id) || null;
});

const requiresReturnDate = computed(() => {
  if (!selectedProduct.value) return false;
  return ['equipos', 'herramientas'].includes(selectedProduct.value.tipo_material);
});

const tipoMaterialLabels = {
  equipos: 'Equipos',
  herramientas: 'Herramientas',
  consumibles: 'Consumibles',
};

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
  form.inventory_id = null;
  form.quantity = 1;
  form.return_date = '';
  form.clearErrors();
  generalError.value = '';
});

watch(() => form.inventory_id, () => {
  if (activeTab.value === 'inventario') {
    form.quantity = 1;
  }
  form.clearErrors();
  generalError.value = '';
});

watch(activeTab, (tab) => {
  form.request_type = tab;
  if (tab === 'compra') {
    form.inventory_id = null;
  } else {
    form.quantity = 1;
  }
  form.clearErrors();

  if (tab === 'inventario' && form.product_id) {
    const productId = Number(form.product_id);
    if (!availableInventoryProductIds.value.has(productId)) {
      form.product_id = '';
      form.inventory_id = null;
      form.return_date = '';
    }
  }
});

watch(displayedProducts, (products) => {
  const productId = Number(form.product_id);
  if (!form.product_id) return;
  if (!products.some(product => product.id === productId)) {
    form.product_id = '';
    form.inventory_id = null;
  }
});

watch(requiresReturnDate, (required) => {
  if (!required) {
    form.return_date = '';
    form.clearErrors('return_date');
  }
});

watch(() => form.return_date, () => {
  if (form.errors.return_date) {
    form.clearErrors('return_date');
  }
});

watch(() => form.quantity, (value) => {
  if (form.errors.quantity) {
    form.clearErrors('quantity');
  }

  if (value === '' || value === null) return;
  const numeric = Number(value);
  if (!Number.isFinite(numeric) || numeric < 1) {
    form.quantity = 1;
  }
});

function setTab(tab) {
  if (activeTab.value === tab) return;
  activeTab.value = tab;
}

function submit() {
  const payload = {
    ...form.data(),
    request_type: activeTab.value,
    inventory_id: activeTab.value === 'inventario' ? form.inventory_id : null,
    quantity: Number(form.quantity) || 1,
    return_date: requiresReturnDate.value ? (form.return_date || null) : null,
  };

  form.transform(() => payload).post(route('solicitudes.store'), {
    onError: () => {
      generalError.value = 'Corrige los errores del formulario';
    },
    onSuccess: () => {
      successMessage.value = 'Solicitud creada correctamente';
      router.visit(route('solicitudes.index'));
    },
    onFinish: () => {
      form.transform(data => data);
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

      <p class="mb-4 text-sm text-gray-600">
        Todas las solicitudes se crean en estado pendiente y serán canalizadas por la gerencia de Normalización.
      </p>

      <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex gap-6">
          <button
            type="button"
            class="py-3 border-b-2 px-1 text-sm font-medium"
            :class="activeTab === 'inventario' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            @click="setTab('inventario')"
          >
            Inventario
          </button>
          <button
            type="button"
            class="py-3 border-b-2 px-1 text-sm font-medium"
            :class="activeTab === 'compra' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            @click="setTab('compra')"
          >
            Compra
          </button>
        </nav>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Producto -->
        <div>
          <label class="text-sm font-medium">Producto</label>
          <select v-model="form.product_id"
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': form.errors.product_id }">
            <option :value="''">-- Selecciona un producto --</option>
            <option v-for="p in displayedProducts" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <div v-if="form.errors.product_id" class="text-red-600 mt-1 text-sm">{{ form.errors.product_id }}</div>
          <div v-if="selectedProduct" class="text-sm text-gray-600 mt-2">
            Tipo de material: {{ tipoMaterialLabels[selectedProduct.tipo_material] || 'Sin clasificación' }}
          </div>
        </div>

        <!-- Inventario (filtrado por producto) -->
        <div v-if="activeTab === 'inventario'">
          <label class="text-sm font-medium">Inventario (ubicación)</label>
          <select v-model="form.inventory_id"
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': form.errors.inventory_id }">
            <option :value="null">-- Selecciona inventario --</option>
            <option v-for="inv in inventoriesForProduct" :key="inv.id" :value="inv.id">
              {{ inv.location ?? ('ID ' + inv.id) }} — Disponible: {{ (inv.quantity - inv.reserved_quantity) }} (de {{ inv.quantity }})
            </option>
          </select>
          <div v-if="form.errors.inventory_id" class="text-red-600 mt-1 text-sm">{{ form.errors.inventory_id }}</div>
        </div>

        <!-- Cantidad -->
        <div>
          <label class="text-sm font-medium">Cantidad</label>
          <template v-if="activeTab === 'inventario'">
            <select v-model="form.quantity"
                    class="w-full border rounded px-3 py-2"
                    :class="{ 'border-red-500': form.errors.quantity }">
              <option :value="''">-- Selecciona cantidad --</option>
              <option v-for="q in quantityOptions" :key="q" :value="q">{{ q }}</option>
            </select>
          </template>
          <template v-else>
            <input
              v-model.number="form.quantity"
              type="number"
              min="1"
              step="1"
              class="w-full border rounded px-3 py-2"
              :class="{ 'border-red-500': form.errors.quantity }"
              placeholder="Cantidad solicitada"
            />
          </template>
          <div v-if="form.errors.quantity" class="text-red-600 mt-1 text-sm">{{ form.errors.quantity }}</div>
        </div>

        <!-- Fecha de devolución -->
        <div v-if="requiresReturnDate">
          <label class="text-sm font-medium">Fecha de devolución</label>
          <input
            type="date"
            v-model="form.return_date"
            class="w-full border rounded px-3 py-2"
            :class="{ 'border-red-500': form.errors.return_date }"
          />
          <div v-if="form.errors.return_date" class="text-red-600 mt-1 text-sm">{{ form.errors.return_date }}</div>
          <p class="text-xs text-gray-500 mt-1">Ejemplo: 2025-12-31</p>
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

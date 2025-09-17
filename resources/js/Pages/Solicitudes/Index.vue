<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({ solicitudes: Object, flash: Object, auth: Object });

const page = usePage();
const successMessage = ref(page.props.flash?.success || null);

const form = useForm({});
const showModal = ref(false);
const solicitudToUpdate = ref(null);
const modalAction = ref('');

// Confirmar acción (aprobar/rechazar)
function confirmAction(solicitud, action) {
  solicitudToUpdate.value = solicitud;
  modalAction.value = action;
  showModal.value = true;
}

// Ejecutar acción
function updateStatus() {
  if (!solicitudToUpdate.value) return;

  form.transform(() => ({ status: modalAction.value }))
    .post(route('solicitudes.updateStatus', solicitudToUpdate.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        successMessage.value = `Solicitud ${modalAction.value} correctamente`;
        showModal.value = false;
      },
      onError: () => {
        showModal.value = false;
      }
    });
}

onMounted(() => {
  successMessage.value = page.props.flash?.success || null;
});
</script>

<template>
  <AdminLayout title="Solicitudes">

    <!-- Modal de confirmación -->
    <Modal :show="showModal" @close="showModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          ¿Deseas {{ modalAction }} la solicitud {{ solicitudToUpdate?.code }}?
        </h2>
        <div class="mt-6 flex justify-end space-x-4">
          <button @click="showModal = false"
                  class="px-4 py-2 text-sm text-white bg-gray-500 hover:bg-gray-400 rounded-md">
            Cancelar
          </button>
          <button @click="updateStatus"
                  class="px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-md">
            Confirmar
          </button>
        </div>
      </div>
    </Modal>

    <!-- Mensaje de éxito -->
    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
      {{ successMessage }}
    </div>

    <!-- Encabezado y botón crear -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Gestión de Solicitudes</h1>
      <Link :href="route('solicitudes.create')" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
        Nueva Solicitud
      </Link>
    </div>

    <!-- Listado de solicitudes -->
    <div v-if="!solicitudes.data || solicitudes.data.length === 0" class="bg-yellow-100 p-4 rounded">
      No hay solicitudes registradas.
    </div>

    <div v-else class="bg-white shadow rounded-lg border overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 border-b border-gray-300">
          <tr>
            <th class="py-3 px-4">Código</th>
            <th class="py-3 px-4">Producto</th>
            <th class="py-3 px-4">De</th>
            <th class="py-3 px-4">Para</th>
            <th class="py-3 px-4">Estado</th>
            <th class="py-3 px-4">Descripción</th>
            <th class="py-3 px-4 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in solicitudes.data" :key="s.id" class="border-b hover:bg-gray-50">
            <td class="py-3 px-4 font-mono">{{ s.code }}</td>
            <td class="py-3 px-4">{{ s.product?.name }}</td>
            <td class="py-3 px-4">{{ s.from_gerencia?.name }}</td>
            <td class="py-3 px-4">{{ s.to_gerencia?.name }}</td>
            <td class="py-3 px-4">
              <span :class="{
                'px-2 py-1 rounded': true,
                'bg-yellow-200 text-yellow-800': s.status==='pendiente',
                'bg-green-200 text-green-800': s.status==='aprobado',
                'bg-red-200 text-red-800': s.status==='rechazado'
              }">{{ s.status }}
              </span>
            </td>
            <td class="py-3 px-4">{{ s.description }}</td>
            <td class="py-3 px-4 flex justify-center gap-2">
              <button
                v-if="s.status==='pendiente' && auth.user.roles[0]==='super_admin'"
                @click.prevent="confirmAction(s,'aprobado')"
                class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition"
              >
                Aprobar
              </button>
              <button
                v-if="s.status==='pendiente' && auth.user.roles[0]==='super_admin'"
                @click.prevent="confirmAction(s,'rechazado')"
                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition"
              >
                Rechazar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :links="solicitudes.links" class="mt-6" />

  </AdminLayout>
</template>

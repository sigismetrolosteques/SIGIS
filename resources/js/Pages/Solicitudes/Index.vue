<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import {
  EyeIcon,
  CheckCircleIcon,
  XCircleIcon,
  DocumentArrowDownIcon,
  PaperClipIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  solicitudes: Object,
  filters: Object,
  workflow: Object,
  flash: Object,
});

const page = usePage();
const successMessage = ref(page.props.flash?.success || null);
const errorMessage = ref(page.props.flash?.error || null);

const filtersForm = ref({
  search: props.filters?.search ?? '',
  status: props.filters?.status ?? '',
  from_date: props.filters?.from_date ?? '',
  to_date: props.filters?.to_date ?? '',
});

const workflowRole = computed(() => props.workflow?.role ?? 'viewer');
const statusOptions = computed(() => props.workflow?.status_options ?? {});

const isSuperAdmin = computed(() => workflowRole.value === 'super_admin');
const isNormalizacion = computed(() => ['super_admin', 'normalizacion'].includes(workflowRole.value));
const canEditPriority = computed(() => workflowRole.value === 'normalizacion');
const isVpMantenimiento = computed(() => ['super_admin', 'vp_mantenimiento'].includes(workflowRole.value));
const isVpSoporte = computed(() => ['super_admin', 'vp_soporte'].includes(workflowRole.value));
const isAdministracion = computed(() => ['super_admin', 'administracion'].includes(workflowRole.value));
const isCompras = computed(() => ['super_admin', 'compras'].includes(workflowRole.value));
const isViewer = computed(() => workflowRole.value === 'viewer');
const showOriginColumn = computed(() => !isViewer.value);

const showDetalleModal = ref(false);
const detalleSolicitud = ref(null);

const actionModal = ref({
  visible: false,
  solicitud: null,
  action: '',
  title: '',
  description: '',
});

const actionForm = useForm({
  action: '',
  justification: '',
  priority: '',
  approved_quantity: '',
  attachment: null,
});

const attachmentInput = ref(null);
const actionError = ref(null);
const baseActionClass = 'inline-flex h-10 w-10 items-center justify-center rounded-full transition text-xs font-medium';
const finalStatuses = ['aprobado', 'rechazado'];
const priorityLabels = {
  alto: 'Alto',
  medio: 'Medio',
  bajo: 'Bajo',
};
const priorityOptions = [
  { value: 'alto', label: 'Alto - Esta solicitud tiene un nivel de urgencia o requerimiento alto.' },
  { value: 'medio', label: 'Medio - Esta solicitud tiene un nivel de urgencia o requerimiento medio.' },
  { value: 'bajo', label: 'Bajo - Esta solicitud tiene un nivel de urgencia o requerimiento bajo.' },
];

function formatStatus(status, viewerMask = false) {
  const labels = statusOptions.value || {};
  if (viewerMask && ['pendiente', 'vp_mantenimiento', 'vp_soporte', 'administracion', 'compras', 'normalizacion_final'].includes(status)) {
    return labels[status] || 'En revisión';
  }
  return labels[status] ?? status;
}

function formatPriority(priority) {
  return priorityLabels[priority] ?? 'Sin definir';
}

function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return '-';
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}`;
}

function formatShortDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return dateString;
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

function statusBadgeClass(status) {
  const base = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border';
  switch (status) {
    case 'pendiente':
      return `${base} bg-status-pending-bg text-status-pending-text border-status-pending-brd`;
    case 'vp_mantenimiento':
      return `${base} bg-status-pending-bg text-status-pending-text border-status-pending-brd`;
    case 'vp_soporte':
      return `${base} bg-status-pending-bg text-status-pending-text border-status-pending-brd`;
    case 'administracion':
      return `${base} bg-status-pending-bg text-status-pending-text border-status-pending-brd`;
    case 'compras':
      return `${base} bg-status-pending-bg text-status-pending-text border-status-pending-brd`;
    case 'normalizacion_final':
      return `${base} bg-status-pending-bg text-status-pending-text border-status-pending-brd`;
    case 'aprobado':
      return `${base} bg-status-approved-bg text-status-approved-text border-status-approved-brd`;
    case 'rechazado':
      return `${base} bg-status-danger-bg text-status-danger-text border-status-danger-brd`;
    default:
      return `${base} bg-gray-100 text-gray-800 border-gray-200`;
  }
}

function applyFilters() {
  router.get(
    route('solicitudes.index'),
    {
      search: filtersForm.value.search || undefined,
      status: filtersForm.value.status || undefined,
      from_date: filtersForm.value.from_date || undefined,
      to_date: filtersForm.value.to_date || undefined,
    },
    { preserveState: true, preserveScroll: true, replace: true }
  );
}

function resetFilters() {
  filtersForm.value = {
    search: '',
    status: '',
    from_date: '',
    to_date: '',
  };
  applyFilters();
}

function openDetalle(solicitud) {
  detalleSolicitud.value = solicitud;
  showDetalleModal.value = true;
}

const actionLabels = {
  approve_stage: 'Aprobar etapa',
  reject_stage: 'Rechazar etapa',
  compras_approve: 'Aprobación de Compras',
  compras_reject: 'Devolver a Administración',
  finalize_order: 'Generar Nota de Entrega',
};

function getStatusLabel(status) {
  return statusOptions.value?.[status] ?? status;
}

function openActionModal(solicitud, action) {
  actionModal.value = {
    visible: true,
    solicitud,
    action,
    title: actionLabels[action] || 'Acción',
    description: getStatusLabel(solicitud.status),
  };
  actionForm.action = action;
  actionForm.justification = '';
  actionForm.priority = solicitud?.priority ?? '';
  actionForm.approved_quantity = action === 'compras_approve'
    ? remainingQuantity(solicitud)
    : '';
  actionForm.attachment = null;
  if (attachmentInput.value) {
    attachmentInput.value.value = '';
  }
  actionError.value = null;
}

function closeActionModal() {
  actionModal.value.visible = false;
  actionModal.value.solicitud = null;
  actionForm.reset();
  actionForm.attachment = null;
  if (attachmentInput.value) {
    attachmentInput.value.value = '';
  }
  actionError.value = null;
}

function handleAttachmentChange(event) {
  const [file] = event.target.files;
  actionForm.attachment = file ?? null;
}

function submitAction() {
  if (!actionModal.value.solicitud) return;

  const payload = {
    ...actionForm.data(),
    approved_quantity: actionModal.value.action === 'compras_approve'
      ? actionForm.approved_quantity
      : null,
    priority: actionModal.value.action === 'approve_stage'
      ? (actionForm.priority || null)
      : null,
  };

  actionForm.transform(() => payload).post(route('solicitudes.updateStatus', actionModal.value.solicitud.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      successMessage.value = 'Solicitud actualizada correctamente';
      closeActionModal();
    },
    onError: (errors) => {
      actionError.value = errors?.approved_quantity
        || errors?.priority
        || errors?.attachment
        || errors?.justification
        || errors?.action
        || errors?.inventory
        || errors?.general
        || 'No se pudo completar la acción.';
    },
    onFinish: () => {
      actionForm.transform(data => data);
    },
  });
}

function openPDF(solicitud) {
  if (!solicitud) return;
  window.open(route('solicitudes.pdf', solicitud.id), '_blank');
}

function pdfLabel(solicitud) {
  if (!solicitud) return 'Generar PDF';
  return solicitud.status === 'aprobado'
    ? 'Generar Nota de Entrega PDF'
    : 'Generar Minuta PDF';
}

function remainingQuantity(solicitud) {
  const quantity = Number(solicitud.quantity) || 0;
  const approved = Number(solicitud.approved_quantity) || 0;
  return Math.max(0, quantity - approved);
}

function canApproveStage(solicitud) {
  if (!solicitud || finalStatuses.includes(solicitud.status)) return false;
  switch (solicitud.status) {
    case 'pendiente':
      return isNormalizacion.value || isSuperAdmin.value;
    case 'vp_mantenimiento':
      return isVpMantenimiento.value || isSuperAdmin.value;
    case 'vp_soporte':
      return isVpSoporte.value || isSuperAdmin.value;
    case 'administracion':
      return isAdministracion.value || isSuperAdmin.value;
    default:
      return false;
  }
}

function canRejectStage(solicitud) {
  if (!solicitud || finalStatuses.includes(solicitud.status)) return false;
  switch (solicitud.status) {
    case 'pendiente':
      return isNormalizacion.value || isSuperAdmin.value;
    case 'vp_mantenimiento':
      return isVpMantenimiento.value || isSuperAdmin.value;
    case 'vp_soporte':
      return isVpSoporte.value || isSuperAdmin.value;
    case 'administracion':
      return isAdministracion.value || isSuperAdmin.value;
    case 'normalizacion_final':
      return isNormalizacion.value || isSuperAdmin.value;
    default:
      return false;
  }
}

function canComprasApprove(solicitud) {
  if (!solicitud || finalStatuses.includes(solicitud.status)) return false;
  if (!(isCompras.value || isSuperAdmin.value)) return false;
  if (!['compras', 'normalizacion_final'].includes(solicitud.status)) return false;
  return remainingQuantity(solicitud) > 0;
}

function canComprasReject(solicitud) {
  if (!solicitud || finalStatuses.includes(solicitud.status)) return false;
  if (!(isCompras.value || isSuperAdmin.value)) return false;
  return ['compras', 'normalizacion_final'].includes(solicitud.status);
}

function canFinalizeOrder(solicitud) {
  if (!solicitud) return false;
  if (!(isNormalizacion.value || isSuperAdmin.value)) return false;
  return solicitud.status === 'normalizacion_final' && remainingQuantity(solicitud) === 0;
}

function timelineLabel(action) {
  switch (action) {
    case 'created':
      return 'Solicitud creada';
    case 'approve_stage':
      return 'Aprobación de etapa';
    case 'reject_stage':
      return 'Rechazo de etapa';
    case 'compras_approve':
      return 'Aprobación de Compras';
    case 'compras_reject':
      return 'Devolución de Compras';
    case 'finalize_order':
      return 'Orden de entrega';
    case 'inventory_finalized':
      return 'Inventario aprobado';
    default:
      return action;
  }
}

function attachmentUrl(log) {
  if (!log?.id || !detalleSolicitud?.value?.id) return null;
  return route('solicitudes.attachment', {
    solicitud: detalleSolicitud.value.id,
    log: log.id,
  });
}
</script>

<template>
  <AdminLayout title="Solicitudes">
    <Modal :show="showDetalleModal" @close="showDetalleModal = false">
      <div class="p-6 bg-white rounded-lg max-w-3xl">
        <h2 class="text-xl font-bold mb-4">Detalle de la solicitud</h2>
        <div v-if="detalleSolicitud">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
              <p class="text-sm text-gray-500">Código</p>
              <p class="font-mono font-semibold text-gray-800">{{ detalleSolicitud.code }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Fecha</p>
              <p class="text-gray-800">{{ formatDate(detalleSolicitud.created_at) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Producto</p>
              <p class="text-gray-800">{{ detalleSolicitud.product?.name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Cantidad</p>
              <p class="text-gray-800">{{ detalleSolicitud.quantity }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Prioridad</p>
              <p class="text-gray-800">{{ formatPriority(detalleSolicitud.priority) }}</p>
            </div>
            <div v-if="!isViewer">
              <p class="text-sm text-gray-500">Origen</p>
              <p class="text-gray-800">{{ detalleSolicitud.from_gerencia?.name ?? 'Sin gerencia' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Estado actual</p>
              <p class="text-gray-800">{{ formatStatus(detalleSolicitud.status) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Cantidad aprobada</p>
              <p class="text-gray-800">{{ detalleSolicitud.approved_quantity }} / {{ detalleSolicitud.quantity }}</p>
            </div>
            <div v-if="detalleSolicitud.return_date">
              <p class="text-sm text-gray-500">Fecha de devolución</p>
              <p class="text-gray-800">{{ formatShortDate(detalleSolicitud.return_date) }}</p>
            </div>
            <div class="md:col-span-2">
              <p class="text-sm text-gray-500">Descripción</p>
              <p class="text-gray-800 whitespace-pre-line">{{ detalleSolicitud.description || 'Sin descripción' }}</p>
            </div>
          </div>

          <h3 class="text-lg font-semibold mb-3">Historial de estatus</h3>
          <ul class="space-y-3 max-h-64 overflow-y-auto pr-2">
            <li
              v-for="log in detalleSolicitud.status_logs"
              :key="log.id"
              class="border border-gray-200 rounded-lg p-3 bg-gray-50"
            >
              <div class="flex flex-wrap justify-between text-sm text-gray-600 mb-1">
                <span class="font-semibold">{{ timelineLabel(log.action) }}</span>
                <span>{{ formatDate(log.created_at) }}</span>
              </div>
              <div class="text-sm text-gray-700">
                <p>
                  <span class="font-semibold">Estatus:</span>
                  {{ formatStatus(log.status) }}
                </p>
                <p>
                  <span class="font-semibold">Responsable:</span>
                  {{ log.user?.name ?? 'Sistema' }}
                </p>
                <p>
                  <span class="font-semibold">Justificación:</span>
                  {{ log.justification || 'Sin justificación' }}
                </p>
                <p v-if="log.approved_quantity">
                  <span class="font-semibold">Cantidad aprobada:</span>
                  {{ log.approved_quantity }}
                </p>
                <div v-if="log.attachment_path" class="mt-2">
                  <a
                    :href="attachmentUrl(log)"
                    target="_blank"
                    class="inline-flex items-center gap-1 text-brand-red hover:text-brand-wine"
                  >
                    <PaperClipIcon class="w-4 h-4" />
                    Ver documento adjunto
                  </a>
                </div>
              </div>
            </li>
          </ul>

          <div class="mt-6 flex justify-end">
            <button
              type="button"
              class="px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-brand-wine transition"
              @click="openPDF(detalleSolicitud)"
            >
              {{ pdfLabel(detalleSolicitud) }}
            </button>
          </div>
        </div>
      </div>
    </Modal>

    <Modal :show="actionModal.visible" @close="closeActionModal">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900">{{ actionModal.title }}</h2>
        <p class="text-sm text-gray-600 mb-4">
          Ingresa la justificación para continuar con la acción seleccionada.
        </p>
        <p v-if="actionModal.description" class="text-xs text-gray-500 mb-4">
          Etapa actual: {{ actionModal.description }}
        </p>
        <div v-if="actionError" class="mb-3 p-3 bg-red-100 text-red-700 rounded">
          {{ actionError }}
        </div>
        <form @submit.prevent="submitAction" class="space-y-4">
          <div>
            <label class="text-sm font-medium text-gray-700">Justificación</label>
            <textarea
              v-model="actionForm.justification"
              class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
              rows="3"
              placeholder="Describe la razón de la acción"
              required
            ></textarea>
          </div>
          <div v-if="actionForm.action === 'approve_stage'">
            <label class="text-sm font-medium text-gray-700">Prioridad</label>
            <select
              v-model="actionForm.priority"
              class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
              :disabled="!canEditPriority"
              :required="canEditPriority"
            >
              <option value="">Sin definir</option>
              <option
                v-for="option in priorityOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
            <p v-if="!canEditPriority" class="text-xs text-gray-500 mt-1">
              Solo Normalización puede establecer la prioridad.
            </p>
          </div>
          <div v-if="actionForm.action === 'compras_approve'">
            <label class="text-sm font-medium text-gray-700">Cantidad aprobada</label>
            <input
              type="number"
              v-model.number="actionForm.approved_quantity"
              :max="remainingQuantity(actionModal.solicitud)"
              min="1"
              step="1"
              class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
              placeholder="Cantidad a aprobar"
              required
            />
            <p class="text-xs text-gray-500 mt-1">
              Cantidad pendiente: {{ remainingQuantity(actionModal.solicitud) }}
            </p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Adjuntar documento (opcional)</label>
            <input
              ref="attachmentInput"
              type="file"
              class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
              accept="application/pdf,image/*"
              @change="handleAttachmentChange"
            />
            <p class="text-xs text-gray-500 mt-1">Formatos sugeridos: PDF, imágenes o documentos.</p>
          </div>
          <div class="flex justify-end gap-3 flex-wrap">
            <button
              type="button"
              class="px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-brand-wine transition disabled:opacity-50"
              :disabled="!actionModal.solicitud"
              @click="openPDF(actionModal.solicitud)"
            >
              {{ pdfLabel(actionModal.solicitud) }}
            </button>
            <button
              type="button"
              class="px-4 py-2 bg-brand-gray text-brand-charcoal rounded-lg hover:bg-gray-200 transition"
              @click="closeActionModal"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-brand-wine transition disabled:opacity-50"
              :disabled="actionForm.processing"
            >
              {{ actionForm.processing ? 'Guardando...' : 'Confirmar' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <div v-if="successMessage" class="mb-4 p-4 bg-brand-gray border border-brand-red/40 text-brand-charcoal rounded">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
      {{ errorMessage }}
    </div>

   

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-brand-charcoal">Gestión de Solicitudes</h1>
      <Link
        :href="route('solicitudes.create')"
        class="inline-flex items-center bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-brand-wine transition"
      >
        Nueva Solicitud
      </Link>
    </div>
     <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5">
      <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="md:col-span-2">
          <label class="text-sm font-medium text-gray-600">Buscar</label>
          <input
            v-model="filtersForm.search"
            type="text"
            placeholder="Código o producto"
            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-red focus:outline-none"
          />
        </div>
        <div>
          <label class="text-sm font-medium text-gray-600">Estado</label>
          <select
            v-model="filtersForm.status"
            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-red focus:outline-none"
          >
            <option value="">Todos</option>
            <option v-for="(label, value) in statusOptions" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-600">Desde</label>
          <input
            v-model="filtersForm.from_date"
            type="date"
            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-red focus:outline-none"
          />
        </div>
        <div>
          <label class="text-sm font-medium text-gray-600">Hasta</label>
          <input
            v-model="filtersForm.to_date"
            type="date"
            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-red focus:outline-none"
          />
        </div>
        <div class="flex items-end gap-2">
          <button type="submit" class="px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-brand-wine transition">
            Filtrar
          </button>
          <button
            type="button"
            class="px-4 py-2 bg-brand-gray text-brand-charcoal rounded-lg hover:bg-gray-200 transition"
            @click="resetFilters"
          >
            Limpiar
          </button>
        </div>
      </form>
    </div>
    <div v-if="!solicitudes.data || solicitudes.data.length === 0" class="bg-brand-gray border-l-4 border-brand-red text-brand-charcoal p-4 mb-4">
      No hay solicitudes registradas.
    </div>

    <div
      v-else
      class="bg-white border border-gray-200 rounded-xl shadow-sm flex flex-col"
    >
      <div class="relative max-h-[60vh] overflow-auto">
        <table class="min-w-[960px] divide-y divide-gray-200 text-sm text-gray-700">
          <thead class="bg-brand-gray text-gray-700 sticky top-0 z-10">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-48">
                Acciones
              </th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">
                Código
              </th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                Producto
              </th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-28">
                Cantidad
              </th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">
                Aprobado
              </th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-48">
                Fecha
              </th>
              <th
                v-if="showOriginColumn"
                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
              >
                Origen
              </th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">
                Estado
              </th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                Prioridad
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100 bg-white">
            <tr
              v-for="solicitud in solicitudes.data"
              :key="solicitud.id"
              class="hover:bg-brand-gray transition"
            >
              <td class="px-4 py-3 align-top">
                <div class="flex flex-nowrap items-center gap-2 overflow-x-auto pb-1">
                  <button
                    :class="[baseActionClass, 'bg-brand-gray text-brand-charcoal hover:bg-gray-200']"
                    @click="openDetalle(solicitud)"
                    title="Ver detalle"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </button>
                  <template v-if="canApproveStage(solicitud)">
                    <button
                      :class="[baseActionClass, 'bg-brand-tint text-brand-red hover:bg-brand-red/20']"
                      @click="openActionModal(solicitud, 'approve_stage')"
                      title="Aprobar etapa"
                    >
                      <CheckCircleIcon class="w-4 h-4" />
                    </button>
                  </template>

                  <template v-if="canRejectStage(solicitud)">
                    <button
                      :class="[baseActionClass, 'bg-brand-tint text-brand-wine hover:bg-brand-wine/20']"
                      @click="openActionModal(solicitud, 'reject_stage')"
                      title="Rechazar etapa"
                    >
                      <XCircleIcon class="w-4 h-4" />
                    </button>
                  </template>

                  <template v-if="canComprasApprove(solicitud)">
                    <button
                      :class="[baseActionClass, 'bg-brand-tint text-brand-red hover:bg-brand-red/20']"
                      @click="openActionModal(solicitud, 'compras_approve')"
                      title="Aprobación de Compras"
                    >
                      <CheckCircleIcon class="w-4 h-4" />
                    </button>
                  </template>

                  <template v-if="canComprasReject(solicitud)">
                    <button
                      :class="[baseActionClass, 'bg-brand-tint text-brand-wine hover:bg-brand-wine/20']"
                      @click="openActionModal(solicitud, 'compras_reject')"
                      title="Devolver a Administración"
                    >
                      <XCircleIcon class="w-4 h-4" />
                    </button>
                  </template>

                  <template v-if="canFinalizeOrder(solicitud)">
                    <button
                      :class="[baseActionClass, 'bg-brand-tint text-brand-red hover:bg-brand-red/20']"
                      @click="openActionModal(solicitud, 'finalize_order')"
                      title="Generar orden de entrega"
                    >
                      <DocumentArrowDownIcon class="w-4 h-4" />
                    </button>
                  </template>
                </div>
              </td>

              <td class="px-4 py-3 align-top font-mono text-sm text-gray-700 whitespace-nowrap">
                {{ solicitud.code }}
              </td>
              <td class="px-4 py-3 align-top text-sm text-gray-700">
                <span class="block max-w-[16rem] break-words">
                  {{ solicitud.product?.name }}
                </span>
              </td>
              <td class="px-4 py-3 align-top text-sm text-gray-700">
                {{ solicitud.quantity }}
              </td>
              <td class="px-4 py-3 align-top text-sm text-gray-700">
                {{ solicitud.approved_quantity }} / {{ solicitud.quantity }}
              </td>
              <td class="px-4 py-3 align-top text-sm text-gray-700">
                {{ formatDate(solicitud.created_at) }}
              </td>
              <td
                v-if="showOriginColumn"
                class="px-4 py-3 align-top text-sm text-gray-700"
              >
                <span class="block max-w-[12rem] break-words">
                  {{ solicitud.from_gerencia?.name ?? 'Sin gerencia' }}
                </span>
              </td>
              <td class="px-4 py-3 align-top text-sm text-gray-700 whitespace-nowrap">
                <span :class="statusBadgeClass(solicitud.status)">
                  {{ formatStatus(solicitud.status, isViewer.value) }}
                </span>
              </td>
              <td class="px-4 py-3 align-top text-sm text-gray-700">
                <span class="block max-w-[18rem] break-words">
                  {{ formatPriority(solicitud.priority) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>


    <Pagination :links="solicitudes.links" class="mt-6" />
  </AdminLayout>
</template>

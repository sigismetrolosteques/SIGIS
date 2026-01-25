<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  auth: Object,
});

const page = usePage();
const authUser = computed(() => props.auth?.user ?? page.props.auth?.user ?? null);

const sidebarOpen = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const logout = () => {
  router.post(
    route("logout"),
    {},
    {
      preserveScroll: true,
      onSuccess: () => router.visit("/login"),
    }
  );
};

const normalizeRoleName = (roleName) => {
  if (!roleName) return '';
  return String(roleName).trim().toLowerCase().replace(/\s+/g, '_');
};

const userRoles = computed(() => {
  const roles = authUser.value?.roles ?? [];
  return roles
    .map(role => (typeof role === 'string' ? role : role?.name))
    .filter(Boolean)
    .map(normalizeRoleName);
});

const hasRole = role => userRoles.value.includes(role);
const isSuperAdmin = computed(() => hasRole('super_admin'));
const isAdmin = computed(() => hasRole('admin'));
const isViewer = computed(() => !isSuperAdmin.value && !isAdmin.value && hasRole('viewer'));
const workflowRole = computed(() => {
  if (isSuperAdmin.value) return 'super_admin';
  if (!isAdmin.value) return 'viewer';
  const gerenciaName = authUser.value?.gerencia?.name;
  switch (gerenciaName) {
    case 'Normalización':
      return 'normalizacion';
    case 'VP. Mantenimiento':
      return 'vp_mantenimiento';
    case 'VP. Soporte':
      return 'vp_soporte';
    case 'Administración':
      return 'administracion';
    case 'Compras':
      return 'compras';
    default:
      return 'viewer';
  }
});

const displayUserName = computed(() => authUser.value?.name ?? 'Usuario');
const alertsState = ref(page.props.alerts ?? {});
watch(
  () => page.props.alerts,
  (value) => {
    if (value && typeof value === 'object') {
      alertsState.value = value;
    }
  },
  { immediate: true }
);
const alerts = computed(() => alertsState.value ?? {});
const alertsTotal = computed(() => Number(alerts.value.total) || 0);
const canSeeAlerts = computed(() => {
  if (isSuperAdmin.value) return true;
  if (typeof alerts.value.canApprove === 'boolean') return alerts.value.canApprove;
  return workflowRole.value !== 'viewer';
});
const canSeeAlertsModal = computed(() => {
  const allowedRoles = ['normalizacion', 'vp_mantenimiento', 'vp_soporte', 'administracion', 'compras'];
  return allowedRoles.includes(workflowRole.value);
});
const alertsLatestAt = computed(() => {
  const value = alerts.value.latestAt;
  if (!value) return null;
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? null : date;
});
const alertsSeenKey = 'alertsSeenAt';
const alertsSeenTotalKey = 'alertsSeenTotal';
const alertsSeenAt = ref(null);
const alertsSeenTotal = ref(0);
const parseStoredDate = (value) => {
  if (!value) return null;
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? null : date;
};
const parseStoredNumber = (value) => {
  const number = Number(value);
  return Number.isFinite(number) ? number : null;
};
if (typeof window !== 'undefined') {
  alertsSeenAt.value = parseStoredDate(localStorage.getItem(alertsSeenKey));
  const storedSeenTotal = parseStoredNumber(localStorage.getItem(alertsSeenTotalKey));
  alertsSeenTotal.value = storedSeenTotal ?? 0;
}
const hasUnseenAlerts = computed(() => {
  if (!canSeeAlerts.value) return false;
  if (typeof alerts.value.unseen === 'boolean') return alerts.value.unseen;
  if (alertsTotal.value <= 0) return false;
  if (!alertsLatestAt.value) return alertsTotal.value > alertsSeenTotal.value;
  if (!alertsSeenAt.value) return true;
  if (alertsLatestAt.value.getTime() > alertsSeenAt.value.getTime()) return true;
  return alertsTotal.value > alertsSeenTotal.value;
});
const isAlertsPage = computed(() => {
  const url = page.url || '';
  if (url.startsWith('/alertas')) return true;
  if (typeof window === 'undefined') return false;
  return window.location.pathname.startsWith('/alertas');
});
const loginAlertsModalVisible = ref(false);
const loginAlertsModalShown = ref(false);
const closeLoginAlertsModal = () => {
  loginAlertsModalVisible.value = false;
};
const alertToastVisible = ref(false);
let alertsRefreshTimer = null;
let alertToastTimer = null;
const alertsToastKey = 'alertsToastAt';
const alertsToastTotalKey = 'alertsToastTotal';
const alertsToastAt = ref(null);
const alertsToastTotal = ref(0);
if (typeof window !== 'undefined') {
  alertsToastAt.value = parseStoredDate(localStorage.getItem(alertsToastKey));
  const storedToastTotal = parseStoredNumber(localStorage.getItem(alertsToastTotalKey));
  alertsToastTotal.value = storedToastTotal ?? 0;
}

const alertToastDetails = computed(() => {
  if (alertsTotal.value <= 0) return '';
  return 'Revisa la bandeja de aprobaciones.';
});

const triggerAlertToast = () => {
  alertToastVisible.value = true;
  if (alertToastTimer) clearTimeout(alertToastTimer);
  alertToastTimer = setTimeout(() => {
    alertToastVisible.value = false;
  }, 4500);
};

const persistToastAt = (value, total = alertsTotal.value) => {
  alertsToastAt.value = value;
  alertsToastTotal.value = Number(total) || 0;
  if (typeof window !== 'undefined') {
    if (!value) {
      localStorage.removeItem(alertsToastKey);
    } else {
      localStorage.setItem(alertsToastKey, value.toISOString());
    }
    if (alertsToastTotal.value <= 0) {
      localStorage.removeItem(alertsToastTotalKey);
    } else {
      localStorage.setItem(alertsToastTotalKey, String(alertsToastTotal.value));
    }
  }
};

const persistAlertsSeen = (value, total = alertsTotal.value) => {
  alertsSeenAt.value = value;
  alertsSeenTotal.value = Number(total) || 0;
  if (typeof window !== 'undefined') {
    if (!value) {
      localStorage.removeItem(alertsSeenKey);
    } else {
      localStorage.setItem(alertsSeenKey, value.toISOString());
    }
    if (alertsSeenTotal.value <= 0) {
      localStorage.removeItem(alertsSeenTotalKey);
    } else {
      localStorage.setItem(alertsSeenTotalKey, String(alertsSeenTotal.value));
    }
  }
};

const markAlertsSeen = () => {
  const seenAt = alertsLatestAt.value ?? new Date();
  persistAlertsSeen(seenAt, alertsTotal.value);
  persistToastAt(seenAt, alertsTotal.value);
  alertToastVisible.value = false;
  alertsState.value = { ...alertsState.value, unseen: false };
};

const markAlertsSeenWithPayload = (payload) => {
  const total = Number(payload?.total) || alertsTotal.value;
  let seenAt = alertsLatestAt.value ?? new Date();
  if (payload?.latestAt) {
    const payloadDate = new Date(payload.latestAt);
    if (!Number.isNaN(payloadDate.getTime())) {
      seenAt = payloadDate;
    }
  }
  persistAlertsSeen(seenAt, total);
  persistToastAt(seenAt, total);
  alertToastVisible.value = false;
  alertsState.value = { ...alertsState.value, unseen: false };
};

const fetchAlerts = async (options = {}) => {
  if (!canSeeAlerts.value) return;
  try {
    const response = await fetch('/alertas/resumen', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!response.ok) return;
    const data = await response.json();
    if (data && typeof data === 'object') {
      alertsState.value = data;
      if (options.markSeen) {
        markAlertsSeenWithPayload(data);
      }
    }
  } catch (error) {
    // ignore fetch errors to keep UI responsive
  }
};

const canAccess = roles => {
  if (!Array.isArray(roles) || roles.length === 0) return true;
  if (roles.includes('super_admin') && isSuperAdmin.value) return true;
  if (roles.includes('admin') && (isAdmin.value || isSuperAdmin.value)) return true;
  if (roles.includes('viewer') && (isViewer.value || isAdmin.value || isSuperAdmin.value)) return true;
  return false;
};

const menuItems = computed(() => [
  { href: '/user', label: 'Gestión de Usuarios', roles: ['super_admin', 'admin'], icon: 'users' },
  { href: '/product', label: 'Gestión de Productos', roles: ['super_admin', 'admin', 'viewer'], icon: 'cube' },
  { href: '/inventory', label: 'Gestión de Inventario', roles: ['super_admin', 'admin', 'viewer'], icon: 'archive' },
  { href: '/solicitudes', label: 'Gestión de Solicitudes', roles: ['super_admin', 'admin', 'viewer'], icon: 'inbox' },
  { href: '/reportes/solicitudes', label: 'Gestión de Reportes', roles: ['super_admin', 'admin', 'viewer'], icon: 'chart' },
  { href: '/auditoria', label: 'Auditoría', roles: ['super_admin'], icon: 'clipboard' },
  { href: '/profile', label: 'Perfil', roles: ['super_admin', 'admin', 'viewer'], icon: 'user' },
]);

const mainClasses = computed(() => [
  "flex flex-col flex-1 min-h-screen transition-all duration-300 ease-in-out",
  sidebarOpen.value ? "sm:ml-64" : "sm:ml-0",
]);

watch(isAlertsPage, (onAlerts) => {
  if (!canSeeAlerts.value) return;
  if (onAlerts) {
    fetchAlerts({ markSeen: true });
  }
});

watch([alertsLatestAt, alertsTotal], ([latestAt, total]) => {
  if (!canSeeAlerts.value) return;
  if (total <= 0 || isAlertsPage.value || !hasUnseenAlerts.value) {
    return;
  }

  const toastTotal = Number(alertsToastTotal.value) || 0;
  const shouldToastByTime = latestAt
    && (!alertsToastAt.value || latestAt.getTime() > alertsToastAt.value.getTime());
  const shouldToastByCount = total > toastTotal;

  if (shouldToastByTime || shouldToastByCount) {
    triggerAlertToast();
    persistToastAt(latestAt ?? new Date(), total);
  }
}, { immediate: true });

watch([alertsTotal, alertsLatestAt, isAlertsPage], ([total, latestAt, onAlerts]) => {
  if (!canSeeAlerts.value) return;
  if (total === 0) {
    persistAlertsSeen(null, 0);
    persistToastAt(null, 0);
    return;
  }

  if (onAlerts && total > 0) {
    alertToastVisible.value = false;
    markAlertsSeen();
    return;
  }

  if (latestAt && alertsToastAt.value && latestAt.getTime() < alertsToastAt.value.getTime()) {
    persistToastAt(latestAt, Math.min(alertsToastTotal.value || 0, total));
  }

  if (latestAt && alertsSeenAt.value && latestAt.getTime() < alertsSeenAt.value.getTime()) {
    persistAlertsSeen(latestAt, Math.min(alertsSeenTotal.value || 0, total));
  }

  if (alertsToastTotal.value > total) {
    persistToastAt(alertsToastAt.value ?? latestAt ?? new Date(), total);
  }

  if (alertsSeenTotal.value > total) {
    persistAlertsSeen(alertsSeenAt.value ?? latestAt ?? new Date(), total);
  }
}, { immediate: true });

const showLoginAlertsModal = () => {
  if (isAlertsPage.value || loginAlertsModalShown.value) return;
  if (!canSeeAlertsModal.value || alertsTotal.value <= 0) return;
  loginAlertsModalVisible.value = true;
  loginAlertsModalShown.value = true;
  clearLoginAlertsFlag();
};

const shouldShowLoginAlertsModal = () => {
  if (!canSeeAlertsModal.value || alertsTotal.value <= 0) return false;
  if (page.props.flash?.showAlertsModal) return true;
  try {
    return window.sessionStorage?.getItem('showAlertsModalOnLogin') === '1';
  } catch (error) {
    return false;
  }
};

const clearLoginAlertsFlag = () => {
  try {
    window.sessionStorage?.removeItem('showAlertsModalOnLogin');
  } catch (error) {
    // ignore storage errors
  }
};

watch(
  () => page.props.flash?.showAlertsModal,
  (shouldShow) => {
    if (shouldShow) {
      showLoginAlertsModal();
    }
  }
);

onMounted(() => {
  if (shouldShowLoginAlertsModal()) {
    showLoginAlertsModal();
    clearLoginAlertsFlag();
  }

  if (!canSeeAlerts.value) return;
  fetchAlerts({ markSeen: isAlertsPage.value });
  alertsRefreshTimer = setInterval(() => {
    if (document.visibilityState !== 'visible') return;
    fetchAlerts();
  }, 30000);
});

onBeforeUnmount(() => {
  if (alertsRefreshTimer) clearInterval(alertsRefreshTimer);
  if (alertToastTimer) clearTimeout(alertToastTimer);
});
</script>

<template>
  <div class="flex h-screen bg-brand-gray font-sans text-brand-charcoal antialiased">
    <Modal :show="loginAlertsModalVisible" @close="closeLoginAlertsModal" maxWidth="md">
      <div class="p-6">
        <h2 class="text-xl font-semibold text-brand-charcoal">
          Puede que tengas notificaciones pendientes
        </h2>
        <p class="mt-2 text-sm text-brand-muted">
          Revisa la bandeja de alertas para ver las solicitudes asignadas.
        </p>
        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
          <button
            type="button"
            class="inline-flex items-center rounded-full border border-brand-border px-4 py-2 text-sm font-semibold text-brand-charcoal transition hover:bg-brand-gray"
            @click="closeLoginAlertsModal"
          >
            Ahora no
          </button>
          <Link
            href="/alertas"
            class="inline-flex items-center rounded-full bg-brand-red px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-wine"
            @click="markAlertsSeen(); closeLoginAlertsModal();"
          >
            Ir a alertas
          </Link>
        </div>
      </div>
    </Modal>
    <!-- Sidebar -->
    <aside
      :class="[ 
        'relative fixed inset-y-0 left-0 w-64 bg-brand-border-strong text-brand-charcoal border-r border-brand-border-strong shadow-2xl z-50',
        'transform transition-transform duration-300 ease-in-out',
        sidebarOpen.value ? 'translate-x-0' : '-translate-x-full',
        'sm:translate-x-0 sm:static sm:inset-auto',
      ]"
    >
      <div class="pointer-events-none absolute inset-y-0 left-0 w-1 bg-brand-border-strong"></div>
      <div class="relative h-20 flex items-center justify-center border-b border-brand-border-strong bg-brand-border-strong">
        <h1 class="text-4xl font-bold tracking-wider font-mono text-brand-charcoal">
          SIGIS
        </h1>
      </div>

      <!-- Menu -->
<nav class="mt-8 px-4 space-y-2">
  <template v-for="item in menuItems" :key="item.href">
    <Link
      v-if="canAccess(item.roles)"
      :href="item.href"
      class="menu-item"
      :class="$page.url.startsWith(item.href) ? 'menu-active' : 'menu-inactive'"
      @click="item.icon === 'bell' ? markAlertsSeen() : null"
    >
      <svg v-if="item.icon === 'users'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M17 20h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857m0 0a5 5 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>

      <svg v-else-if="item.icon === 'cube'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M20 12V6a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4" />
      </svg>

      <svg v-else-if="item.icon === 'archive'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7v13h14V7M9 11h6" />
      </svg>

      <svg v-else-if="item.icon === 'inbox'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6l3 4h10l3-4z" />
      </svg>

      <svg v-else-if="item.icon === 'chart'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 19h16M7 10h3v9H7zM14 5h3v14h-3z" />
      </svg>

      <svg v-else-if="item.icon === 'bell'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0m6 0H9" />
      </svg>

      <svg v-else-if="item.icon === 'clipboard'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 2h6a2 2 0 012 2v1h1a2 2 0 012 2v13a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h1V4a2 2 0 012-2z" />
      </svg>

      <svg v-else-if="item.icon === 'user'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>

      <span class="font-medium truncate flex-1">{{ item.label }}</span>
      <span
        v-if="item.icon === 'bell' && hasUnseenAlerts"
        class="ml-auto inline-flex min-w-[1.5rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold"
        :class="hasUnseenAlerts
          ? 'bg-brand-red text-white'
          : 'bg-white text-brand-muted border border-brand-border-strong'"
      >
        {{ alertsTotal }}
      </span>
    </Link>
  </template>
</nav>


      <div class="mt-auto px-4 py-5">
        <div class="rounded-lg p-3 flex items-center justify-center">
          <img
            src="/img/Metro_Los_Teques_Logo_Boceto.svg.png"
            alt="Ministerio del Transporte"
            class="h-12 w-auto object-contain"
          />
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div :class="mainClasses">
      <Transition name="alert-toast">
        <div
          v-if="canSeeAlerts && alertToastVisible && alertsTotal > 0"
          class="fixed top-5 right-6 z-50 w-80 rounded-xl border border-brand-red/30 bg-brand-tint px-4 py-3 shadow-lg"
        >
          <div class="flex items-start gap-3">
            <div class="mt-1 h-2.5 w-2.5 rounded-full bg-brand-red"></div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-brand-charcoal">
                Tienes {{ alertsTotal }} solicitud{{ alertsTotal === 1 ? '' : 'es' }} pendiente{{ alertsTotal === 1 ? '' : 's' }} por aprobar.
              </p>
              <p v-if="alertToastDetails" class="text-xs text-brand-muted mt-1">
                {{ alertToastDetails }}
              </p>
              <Link href="/alertas" class="mt-2 inline-flex text-xs font-semibold text-brand-red hover:text-brand-wine underline">
                Ver solicitudes
              </Link>
            </div>
            <button
              type="button"
              class="text-brand-muted hover:text-brand-red"
              @click="alertToastVisible = false"
              aria-label="Cerrar notificación"
            >
              ✕
            </button>
          </div>
        </div>
      </Transition>
      <header
        class="flex items-center justify-between h-16 px-6 bg-white/90 backdrop-blur border-b border-brand-border shadow-sm"
      >
        <div class="flex items-center">
          <button
            class="sm:hidden text-brand-muted hover:text-brand-red transition-colors p-1 rounded-full hover:bg-white/70"
            @click="toggleSidebar"
            aria-label="Toggle sidebar"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <div class="ml-4 flex items-center gap-2 bg-white/80 border border-brand-border-strong px-3 py-1.5 rounded-full shadow-sm">
            <span class="h-2 w-2 rounded-full bg-brand-red"></span>
            <span class="text-brand-charcoal font-semibold">Hola, {{ displayUserName }}</span>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Link
            v-if="canSeeAlerts"
            href="/alertas"
            class="relative inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-medium shadow-sm transition"
            :class="hasUnseenAlerts
              ? 'border-brand-red bg-brand-red text-white hover:bg-brand-wine'
              : 'border-brand-border bg-white text-brand-charcoal hover:bg-brand-gray'"
            @click="markAlertsSeen"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0m6 0H9" />
            </svg>
            <span>Aprobaciones</span>
            <span
              v-if="hasUnseenAlerts"
              class="inline-flex min-w-[1.5rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold"
              :class="hasUnseenAlerts
                ? 'bg-white/20 text-white'
                : 'bg-brand-gray text-brand-muted border border-brand-border-strong'"
            >
              {{ alertsTotal }}
            </span>
          </Link>

          <button
            @click="logout"
            class="flex items-center text-sm text-brand-muted hover:text-brand-red transition-colors"
          >
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Salir
          </button>
        </div>
      </header>

      <main class="flex-1 p-6 bg-brand-gray overflow-y-auto">
        <div class="max-w-7xl mx-auto">
          <div class="bg-brand-tint/60 rounded-2xl shadow-lg border border-brand-red/20 overflow-hidden ring-1 ring-brand-red/10">
            <div class="p-1 bg-gradient-to-r from-brand-tint via-white to-brand-gray"></div>
            <div class="p-6">
              <slot />
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
.menu-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.75rem 1rem;
  border-left: 4px solid transparent;
  border-radius: 0.5rem;
  transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.menu-active {
  background-color: #AB2423;
  color: #FFFFFF;
  border-left-color: #FFFFFF;
}

.menu-inactive {
  color: #111827;
}

.menu-inactive:hover {
  background-color: #C7CCD4;
  color: #111827;
  border-left-color: #D1D5DB;
}

.menu-item,
.menu-item * {
  transition: none;
  box-shadow: none;
}

.alert-toast-enter-active,
.alert-toast-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.alert-toast-enter-from,
.alert-toast-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>

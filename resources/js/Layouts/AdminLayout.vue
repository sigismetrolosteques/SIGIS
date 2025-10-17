<script setup>
import { ref, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";

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

const userRoles = computed(() => {
  const roles = authUser.value?.roles ?? [];
  return roles
    .map(role => (typeof role === 'string' ? role : role?.name))
    .filter(Boolean);
});

const hasRole = role => userRoles.value.includes(role);
const isSuperAdmin = computed(() => hasRole('super_admin'));
const isAdmin = computed(() => hasRole('admin'));
const isViewer = computed(() => !isSuperAdmin.value && !isAdmin.value && hasRole('viewer'));

const displayUserName = computed(() => authUser.value?.name ?? 'Usuario');

const canAccess = roles => {
  if (!Array.isArray(roles) || roles.length === 0) return true;
  if (roles.includes('super_admin') && isSuperAdmin.value) return true;
  if (roles.includes('admin') && (isAdmin.value || isSuperAdmin.value)) return true;
  if (roles.includes('viewer') && (isViewer.value || isAdmin.value || isSuperAdmin.value)) return true;
  return false;
};

const menuItems = computed(() => [
  { href: '/user', label: 'Gestión de Usuarios', roles: ['super_admin'], icon: 'users' },
  { href: '/product', label: 'Gestión de Productos', roles: ['super_admin', 'admin', 'viewer'], icon: 'cube' },
  { href: '/inventory', label: 'Gestión de Inventario', roles: ['super_admin', 'admin', 'viewer'], icon: 'archive' },
  { href: '/solicitudes', label: 'Gestión de Solicitudes', roles: ['super_admin', 'admin', 'viewer'], icon: 'inbox' },
  { href: '/reportes/solicitudes', label: 'Gestión de Reportes', roles: ['super_admin', 'admin', 'viewer'], icon: 'chart' },
  { href: '/auditoria', label: 'Auditoría', roles: ['super_admin'], icon: 'clipboard' },
  { href: '/profile', label: 'Perfil', roles: ['super_admin', 'admin', 'viewer'], icon: 'user' },
]);

const displayRoleLabel = computed(() => {
  if (isSuperAdmin.value) return 'Super Administrador';
  if (isAdmin.value) return 'Gestor';
  if (isViewer.value) return 'Espectador';
  return 'Usuario';
});

const mainClasses = computed(() => [
  "flex flex-col flex-1 min-h-screen transition-all duration-300 ease-in-out",
  sidebarOpen.value ? "sm:ml-64" : "sm:ml-0",
]);
</script>

<template>
  <div class="flex h-screen bg-gray-50 font-sans text-gray-800 antialiased">
    <!-- Sidebar -->
    <aside
      :class="[ 
        'fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-gray-500 to-gray-600 shadow-2xl z-50',
        'transform transition-transform duration-300 ease-in-out',
        sidebarOpen.value ? 'translate-x-0' : '-translate-x-full',
        'sm:translate-x-0 sm:static sm:inset-auto',
      ]"
    >
      <div class="h-20 flex items-center justify-center border-b border-red-500">
        <h1 class="text-2xl font-bold text-white tracking-wider font-mono">
          ADMIN<span class="text-gray-300">PANEL</span>
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

      <svg v-else-if="item.icon === 'clipboard'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 2h6a2 2 0 012 2v1h1a2 2 0 012 2v13a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h1V4a2 2 0 012-2z" />
      </svg>

      <svg v-else-if="item.icon === 'user'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>

      <span class="font-medium truncate">{{ item.label }}</span>
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
      <header
        class="flex items-center justify-between h-16 px-6 bg-white shadow-sm border-b border-gray-200/80"
      >
        <div class="flex items-center">
          <button
            class="sm:hidden text-gray-500 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-gray-100"
            @click="toggleSidebar"
            aria-label="Toggle sidebar"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <div class="ml-4 flex items-center space-x-2 bg-red-100 px-3 py-1.5 rounded-lg">
            <span class="text-gray-800 font-semibold">Hola, {{ displayUserName }}</span>
            <span class="text-sm text-gray-600">{{ displayRoleLabel }}</span>
          </div>
        </div>

        <button
          @click="logout"
          class="flex items-center text-sm text-gray-500 hover:text-red-600 transition-colors"
        >
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Salir
        </button>
      </header>

      <main class="flex-1 p-6 bg-gradient-to-br from-gray-50 to-gray-100 overflow-y-auto">
        <div class="max-w-7xl mx-auto">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden">
            <div class="p-1 bg-gradient-to-r from-red-50 to-gray-50"></div>
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
  background-color: #4b5563;
  color: #ffffff;
  border-left-color: #ef4444;
}

.menu-inactive {
  color: #f3f4f6;
}

.menu-inactive:hover {
  background-color: #4b5563;
  color: #ffffff;
  border-left-color: #ef4444;
}

.menu-item,
.menu-item * {
  transition: none;
  box-shadow: none;
}
</style>

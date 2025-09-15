<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  auth: Object
});

const sidebarOpen = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const logout = () => {
  router.post(route('logout'), {}, {
    preserveScroll: true,
    onSuccess: () => router.visit('/login')
  });
};

const mainClasses = computed(() => [
  'flex flex-col flex-1 min-h-screen transition-all duration-300 ease-in-out',
  sidebarOpen.value ? 'sm:ml-64' : 'sm:ml-0'
]);

// Comprobar si el usuario es super admin
const isSuperAdmin = computed(() => {
  return props.auth?.user?.roles?.[0] === 'super_admin';
});
</script>

<template>
  <div class="flex h-screen bg-gray-50 font-sans text-gray-800 antialiased">
    <!-- Sidebar - Diseño mejorado -->
    <aside :class="[
      'fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-gray-500 to-gray-600 shadow-2xl z-50',
      'transform transition-transform duration-300 ease-in-out',
      sidebarOpen.value ? 'translate-x-0' : '-translate-x-full',
      'sm:translate-x-0 sm:static sm:inset-auto'
    ]">
      <!-- Logo/Cabecera con efecto hover -->
      <div class="h-20 flex items-center justify-center border-b border-red-500 from-red-500  transition-colors duration-200">
        <h1 class="text-2xl font-bold text-white tracking-wider font-mono">ADMIN<span class="text-gray-300">PANEL</span></h1>
      </div>
      <!-- Menú con mejor espaciado y efectos -->
      <nav class="mt-8 px-4 space-y-3">
        <Link 
          v-if=" $page.props.auth.user.roles[0] === 'super_admin'"
          href="/user"
          class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:shadow-lg"
          :class="{
            'bg-gray-600 text-white shadow-md': $page.url.startsWith('/user'),
            'text-red-100 hover:bg-red-600/70': !$page.url.startsWith('/user')
          }"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span class="font-medium">Gestión de Usuarios</span>
        </Link>
        
        <Link 
          v-else
          href="/profile"
          class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:shadow-lg"
          :class="{
            'bg-gray-600 text-white shadow-md': $page.url.startsWith('/profile'),
            'text-red-100 hover:bg-red-600/70': !$page.url.startsWith('/profile')
          }"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="font-medium">Perfil</span>
        </Link>
      </nav>
    </aside>

    <!-- Resto del código permanece igual -->
    <div :class="mainClasses">
      <!-- Header mejorado -->
      <header class="flex items-center justify-between h-16 px-6 bg-white shadow-sm border-b border-gray-200/80">
        <!-- Parte izquierda: Botón de menú y saludo -->
        <div class="flex items-center">
          <button 
            class="sm:hidden text-gray-500 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-gray-100"
            @click="toggleSidebar"
            aria-label="Toggle sidebar"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Avatar con efecto hover -->
          <div class="flex items-center space-x-3 group cursor-pointer ml-4">
            <div class="w-full h-9 p-2 rounded-[20px] bg-red-100 flex items-center justify-center group-hover:bg-red-200 transition-colors duration-200">
              <span class="text-gray-800 font-semibold">Hola, {{ $page.props.auth.user.name }}</span>
              <span class="text-sm text-gray-500 ml-1">
                {{ $page.props.auth.user.roles[0] === 'super_admin' ? 'Administrador' : ($page.props.auth.user.roles[0] === 'admin' ? 'Gestor' : $page.props.auth.user.roles[0]) }}
              </span>
            </div>
            <div class="text-left">
              <p class="text-sm font-medium text-gray-700 group-hover:text-red-600 transition-colors">{{ auth?.user?.name }}</p>
              <p class="text-xs text-gray-500 group-hover:text-gray-600 transition-colors capitalize">{{ auth?.user?.roles?.[0] }}</p>
            </div>
          </div>
        </div>
        
        <!-- Parte derecha: Botón de salir -->
        <div class="flex items-center">
          <button 
            @click="logout"
            class="flex items-center text-sm text-gray-500 hover:text-red-600 transition-colors group"
          >
            <div class="p-1.5 rounded-full group-hover:bg-red-50 mr-1 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </div>
            <span class="font-medium">Salir</span>
          </button>
        </div>
      </header>

      <!-- Área de Contenido con mejor diseño -->
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

      <footer class="py-4 px-6 bg-white border-t border-gray-200/80 text-center text-sm text-gray-500">
        <div class="max-w-7xl mx-auto">
          <img 
            src="/img/MINISTERIO DEL TRANSPORTE.png" 
            alt="Logo Metro Los Teques" 
            class="mx-auto h-20 w-150"
          >
        </div>
      </footer>
    </div>
  </div>
</template>

<style>
/* Efectos de transición mejorados */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Sombras más suaves */
.shadow-sm {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Efecto de hover para elementos interactivos */
.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

// Configuración de colores
const colors = {
  red: {
    light: 'rgba(239, 68, 68, 0.2)',
    medium: 'rgba(239, 68, 68, 0.5)',
    dark: 'rgba(220, 38, 38, 1)'
  },
  gray: {
    light: 'rgba(209, 213, 219, 0.2)',
    medium: 'rgba(156, 163, 175, 0.5)',
    dark: 'rgba(107, 114, 128, 1)'
  }
};

// Datos mejorados para los gráficos
const reportsData = {
  monthly: {
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    datasets: [{
      label: 'Reportes 2025',
      data: [65, 59, 80, 81, 56, 55, 70, 75, 82, 78, 85, 90],
      backgroundColor: colors.red.light,
      borderColor: colors.red.dark,
      borderWidth: 2,
      borderRadius: 4,
      borderSkipped: false
    }]
  },
  users: {
    labels: ['Activos', 'Inactivos', 'Nuevos', 'Suspendidos'],
    datasets: [{
      data: [300, 50, 100, 25],
      backgroundColor: [
        colors.red.medium,
        colors.gray.medium,
        colors.red.dark,
        colors.gray.dark
      ],
      borderWidth: 1
    }]
  }
};

// Referencias para los gráficos
const monthlyChartRef = ref(null);
const usersChartRef = ref(null);

onMounted(() => {
  // Gráfico de barras moderno
  new Chart(monthlyChartRef.value.getContext('2d'), {
    type: 'bar',
    data: reportsData.monthly,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        title: {
          display: true,
          text: 'Reportes Mensuales',
          font: {
            size: 16,
            weight: '600'
          },
          padding: {
            top: 10,
            bottom: 20
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleFont: {
            size: 14,
            weight: 'bold'
          },
          bodyFont: {
            size: 12
          },
          padding: 12,
          cornerRadius: 4,
          displayColors: false
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: colors.gray.dark
          }
        },
        y: {
          grid: {
            color: colors.gray.light,
            drawBorder: false
          },
          ticks: {
            color: colors.gray.dark,
            stepSize: 20
          },
          beginAtZero: true
        }
      }
    }
  });

  // Gráfico de dona moderno
  new Chart(usersChartRef.value.getContext('2d'), {
    type: 'doughnut',
    data: reportsData.users,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: {
          position: 'right',
          labels: {
            padding: 20,
            usePointStyle: true,
            pointStyle: 'circle',
            color: colors.gray.dark
          }
        },
        title: {
          display: true,
          text: 'Distribución de Usuarios',
          font: {
            size: 16,
            weight: '600'
          },
          padding: {
            top: 10,
            bottom: 20
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          bodyFont: {
            size: 12
          },
          padding: 12,
          cornerRadius: 4
        }
      }
    }
  });
});
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Panel de Reportes
      </h2>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Sección de gráficos -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2">
          <!-- Gráfico de reportes mensuales -->
          <div class="p-6 bg-white rounded-xl shadow-sm">
            <div class="h-[350px]">
              <canvas ref="monthlyChartRef"></canvas>
            </div>
          </div>
          
          <!-- Gráfico de usuarios -->
          <div class="p-6 bg-white rounded-xl shadow-sm">
            <div class="h-[350px]">
              <canvas ref="usersChartRef"></canvas>
            </div>
          </div>
        </div>

        <!-- Sección de métricas -->
        <div class="p-6 bg-white rounded-xl shadow-sm">
          <h3 class="text-lg font-semibold text-gray-800">Resumen General</h3>
          <p class="mt-1 text-gray-600">Métricas clave del sistema</p>
          
          <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-4">
            <div class="p-4 border rounded-xl" :style="{ borderColor: colors.red.light }">
              <div class="flex items-center justify-between">
                <h4 class="font-medium text-gray-700">Total Reportes</h4>
                <div class="p-2 rounded-full" :style="{ backgroundColor: colors.red.light }">
                  <svg class="w-5 h-5" :style="{ color: colors.red.dark }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </div>
              </div>
              <p class="mt-2 text-2xl font-bold" :style="{ color: colors.red.dark }">856</p>
              <p class="mt-1 text-sm text-gray-500">+12.5% vs mes anterior</p>
            </div>
            
            <div class="p-4 border rounded-xl" :style="{ borderColor: colors.gray.light }">
              <div class="flex items-center justify-between">
                <h4 class="font-medium text-gray-700">Usuarios Activos</h4>
                <div class="p-2 rounded-full" :style="{ backgroundColor: colors.gray.light }">
                  <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                  </svg>
                </div>
              </div>
              <p class="mt-2 text-2xl font-bold text-gray-700">300</p>
              <p class="mt-1 text-sm text-gray-500">+8.2% vs mes anterior</p>
            </div>
            
            <div class="p-4 border rounded-xl" :style="{ borderColor: colors.red.light }">
              <div class="flex items-center justify-between">
                <h4 class="font-medium text-gray-700">Nuevos Registros</h4>
                <div class="p-2 rounded-full" :style="{ backgroundColor: colors.red.light }">
                  <svg class="w-5 h-5" :style="{ color: colors.red.dark }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                  </svg>
                </div>
              </div>
              <p class="mt-2 text-2xl font-bold" :style="{ color: colors.red.dark }">100</p>
              <p class="mt-1 text-sm text-gray-500">+15.3% vs mes anterior</p>
            </div>
            
            <div class="p-4 border rounded-xl" :style="{ borderColor: colors.gray.light }">
              <div class="flex items-center justify-between">
                <h4 class="font-medium text-gray-700">Tareas Pendientes</h4>
                <div class="p-2 rounded-full" :style="{ backgroundColor: colors.gray.light }">
                  <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                </div>
              </div>
              <p class="mt-2 text-2xl font-bold text-gray-700">24</p>
              <p class="mt-1 text-sm text-gray-500">-3.2% vs mes anterior</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
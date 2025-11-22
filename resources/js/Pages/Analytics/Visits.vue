<template>
  <Head title="Estadísticas de Visitas" />

  <AppLayout>
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Estadísticas de Visitas
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Análisis de las páginas más visitadas del sistema
      </p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <!-- Total Visits -->
      <Card>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Total de Visitas
            </p>
            <p class="text-3xl font-bold mt-2" :style="{ color: 'var(--color-primary)' }">
              {{ totalVisits }}
            </p>
          </div>
          <div class="p-3 rounded-full" :style="{ backgroundColor: 'var(--bg-primary)' }">
            <svg class="w-8 h-8" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
        </div>
      </Card>

      <!-- Total Pages -->
      <Card>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Páginas Visitadas
            </p>
            <p class="text-3xl font-bold mt-2" :style="{ color: 'var(--color-secondary)' }">
              {{ getAllPageVisits.length }}
            </p>
          </div>
          <div class="p-3 rounded-full" :style="{ backgroundColor: 'var(--bg-primary)' }">
            <svg class="w-8 h-8" :style="{ color: 'var(--color-secondary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </Card>

      <!-- Most Visited -->
      <Card>
        <div class="flex items-center justify-between">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Más Visitada
            </p>
            <p class="text-lg font-bold mt-2 truncate" :style="{ color: 'var(--color-accent)' }">
              {{ mostVisitedPage ? getPageDisplayName(mostVisitedPage.page) : 'N/A' }}
            </p>
            <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
              {{ mostVisitedPage ? mostVisitedPage.count : 0 }} visitas
            </p>
          </div>
          <div class="p-3 rounded-full" :style="{ backgroundColor: 'var(--bg-primary)' }">
            <svg class="w-8 h-8" :style="{ color: 'var(--color-accent)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
            </svg>
          </div>
        </div>
      </Card>
    </div>

    <!-- Visits Table -->
    <Card>
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
          Detalle de Visitas por Página
        </h2>
        <button
          @click="handleClearVisits"
          class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors"
        >
          Limpiar Datos
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y" :style="{ borderColor: 'var(--text-secondary)' }">
          <thead>
            <tr :style="{ backgroundColor: 'var(--bg-secondary)' }">
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
                #
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
                Página
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
                Visitas
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
                Porcentaje
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
                Última Visita
              </th>
            </tr>
          </thead>
          <tbody class="divide-y" :style="{ backgroundColor: 'var(--bg-primary)', borderColor: 'var(--text-secondary)' }">
            <tr
              v-for="(visit, index) in getAllPageVisits"
              :key="visit.page"
              class="hover:opacity-75 transition-opacity"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: 'var(--text-primary)' }">
                {{ index + 1 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap" :style="{ color: 'var(--text-primary)' }">
                <div class="flex items-center gap-2">
                  <Badge v-if="index === 0" variant="primary">🏆</Badge>
                  <span class="font-medium">{{ getPageDisplayName(visit.page) }}</span>
                </div>
                <div class="text-xs" :style="{ color: 'var(--text-secondary)' }">
                  {{ visit.page }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[100px]">
                    <div
                      class="h-2 rounded-full transition-all"
                      :style="{ 
                        width: `${(visit.count / totalVisits) * 100}%`,
                        backgroundColor: 'var(--color-primary)'
                      }"
                    ></div>
                  </div>
                  <span class="text-sm font-bold" :style="{ color: 'var(--color-primary)' }">
                    {{ visit.count }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: 'var(--text-secondary)' }">
                {{ ((visit.count / totalVisits) * 100).toFixed(1) }}%
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: 'var(--text-secondary)' }">
                {{ formatDate(visit.lastVisit) }}
              </td>
            </tr>
            <tr v-if="getAllPageVisits.length === 0">
              <td colspan="5" class="px-6 py-8 text-center" :style="{ color: 'var(--text-secondary)' }">
                No hay datos de visitas disponibles
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Card>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { usePageVisits } from '@/composables/usePageVisits';

const {
  totalVisits,
  mostVisitedPage,
  getAllPageVisits,
  clearAllVisits,
  getPageDisplayName,
} = usePageVisits();

const formatDate = (dateString) => {
  if (!dateString) return 'Nunca';
  const date = new Date(dateString);
  return date.toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const handleClearVisits = () => {
  if (confirm('¿Estás seguro de que quieres limpiar todos los datos de visitas?')) {
    clearAllVisits();
    alert('Datos de visitas eliminados correctamente');
  }
};
</script>

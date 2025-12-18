<template>
  <Head title="Business Intelligence" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold leading-tight" :style="{ color: 'var(--text-primary)' }">
          Análisis de Negocio (BI)
        </h2>
        <div class="flex items-center gap-2">
           <Badge variant="primary" class="animate-pulse">Live Data</Badge>
           <p class="text-xs" :style="{ color: 'var(--text-secondary)' }">
             Última actualización: {{ now }}
           </p>
        </div>
      </div>
    </template>

    <div class="py-6 space-y-8">
      <!-- Financial Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <IngresosMensualesChart />
        <DistribucionIngresosChart />
      </div>

      <!-- Professional & Services Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <CitasBarberoChart />
        <ServiciosPopularidadChart />
      </div>

      <!-- Performance Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <IngresosBarberoChart />
        <EstadoCitasChart />
        <MetodosPagoChart />
      </div>

      <!-- Operational Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <StockAlertChart />
        <InventarioTrendsChart />
        <CrecimientoClientesChart />
      </div>

      <!-- Placeholder for more charts in the future -->
      <div class="p-10 border-2 border-dashed rounded-2xl flex flex-col items-center justify-center opacity-40 transition-opacity hover:opacity-100"
           :style="{ borderColor: 'var(--border-color)' }">
         <div class="bg-gray-100 p-4 rounded-full mb-3">
           <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
           </svg>
         </div>
         <p class="font-medium">Nuevas métricas próximamente</p>
         <p class="text-xs text-center mt-1 max-w-xs">Estamos trabajando en análisis predictivos y tendencias de mercado.</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/Badge.vue';
import { ref, onMounted } from 'vue';

// Import All Charts
import CitasBarberoChart from './components/CitasBarberoChart.vue';
import ServiciosPopularidadChart from './components/ServiciosPopularidadChart.vue';
import IngresosMensualesChart from './components/IngresosMensualesChart.vue';
import DistribucionIngresosChart from './components/DistribucionIngresosChart.vue';
import StockAlertChart from './components/StockAlertChart.vue';
import EstadoCitasChart from './components/EstadoCitasChart.vue';
import IngresosBarberoChart from './components/IngresosBarberoChart.vue';
import InventarioTrendsChart from './components/InventarioTrendsChart.vue';
import CrecimientoClientesChart from './components/CrecimientoClientesChart.vue';
import MetodosPagoChart from './components/MetodosPagoChart.vue';

const now = ref(new Date().toLocaleTimeString());

onMounted(() => {
  setInterval(() => {
    now.value = new Date().toLocaleTimeString();
  }, 1000);
});
</script>

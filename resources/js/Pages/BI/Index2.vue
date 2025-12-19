<template>
  <Head title="BI - Analytics Premium" />

  <AppLayout>
    <div class="p-4 sm:p-8 space-y-10 max-w-[1600px] mx-auto min-h-screen bg-transparent">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
          <h2 class="text-4xl font-black tracking-tighter text-gray-900 dark:text-white uppercase italic">
            <span class="text-indigo-600">Estadísticas Faceritos</span>
          </h2>
          <p class="text-sm font-bold text-gray-500 dark:text-gray-400 mt-2 uppercase tracking-[0.3em] opacity-70">
            Panel de Control Estratégico & Analítica Avanzada
          </p>
        </div>
        
        <div class="flex items-center gap-4">
          <button @click="handlePrint" class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 hover:scale-105 transition-all font-black text-xs uppercase tracking-widest">
            <DocumentArrowDownIcon class="w-5 h-5 text-indigo-500" />
            Exportar Reporte PDF
          </button>
        </div>
      </div>

      <!-- Main Analytics Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna Izquierda: Finanzas & Ventas -->
        <div class="lg:col-span-2 space-y-8">
          <GenericLineChart 
            title="Tendencia de Ventas"
            subtitle="Análisis temporal del volumen de transacciones"
            :endpoint="route('bi.stats.sales-status-trend')"
          />

          <GenericLineChart 
            title="Tendencia de Citas"
            subtitle="Evolución de estados (Confirmadas/Canceladas)"
            :endpoint="route('bi.stats.appointments-status-trend')"
          />
        </div>

        <!-- Columna Derecha: Distribución & Top -->
        <div class="space-y-8">
          <GenericPieChart 
            title="Servicios Populares"
            subtitle="Distribución de demanda por tratamiento"
            :endpoint="route('bi.stats.services')"
          />

          <GenericPieChart 
            title="Productos Top"
            subtitle="Ventas por volumen de artículos"
            :endpoint="route('bi.stats.products')"
          />
        </div>
      </div>

      <!-- Operativa & Métodos -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <GenericLineChart 
          title="Flujo de Inventario"
          subtitle="Tendencia temporal de movimientos (Entradas/Salidas)"
          :endpoint="route('bi.stats.inventory-trend')"
        />

        <GenericBarChart 
          title="Métodos de Pago"
          subtitle="Preferencias de transacción de clientes"
          :endpoint="route('bi.stats.payments')"
          :params="{ type: paymentSource }"
          value-name="Uso"
        >
          <template #extra-filters>
            <div class="flex bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
              <button @click="paymentSource = 'citas'" class="px-3 py-1 text-[10px] font-black rounded-md transition-all" :class="paymentSource === 'citas' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-500' : 'opacity-50 text-gray-500'">CITAS</button>
              <button @click="paymentSource = 'ventas'" class="px-3 py-1 text-[10px] font-black rounded-md transition-all" :class="paymentSource === 'ventas' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-500' : 'opacity-50 text-gray-500'">VENTAS</button>
            </div>
          </template>
        </GenericBarChart>
      </section>

      <!-- Resumen de Desempeño -->
      <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <GenericLineChart 
            title="Historial de Ingresos"
            subtitle="Evolución económica bruta"
            :endpoint="route('bi.stats.income')"
            value-name="Ingresos (Bs)"
          />
        </div>
        <div class="space-y-8">
          
          <GenericPieChart 
            title="Usuarios"
            subtitle="Distribución por roles"
            :endpoint="route('bi.stats.role-dist')"
          />
        </div>
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { DocumentArrowDownIcon } from '@heroicons/vue/24/outline';

// Import Generic Components v2
import GenericBarChart from './components2/GenericBarChart.vue';
import GenericPieChart from './components2/GenericPieChart.vue';
import GenericLineChart from './components2/GenericLineChart.vue';

// Local State
const paymentSource = ref('citas');
const handlePrint = () => {
  window.print();
};
</script>

<style scoped>
@media print {
  .flex.items-center.gap-4 { display: none; }
}
</style>

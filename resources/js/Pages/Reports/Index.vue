<template>
  <Head title="Estadísticas" />

  <AppLayout>
    <div class="space-y-8">
      <header>
        <p class="text-sm font-semibold uppercase tracking-wide" :style="{ color: 'var(--text-secondary)' }">
          Panel de control
        </p>
        <h1 class="text-3xl font-bold mt-1" :style="{ color: 'var(--text-primary)' }">
          Estadísticas y Reportes
        </h1>
        <p class="mt-2 max-w-3xl" :style="{ color: 'var(--text-secondary)' }">
          Resumen ejecutivo del desempeño comercial, citas agendadas y salud del inventario para tomar decisiones rápidas.
        </p>
      </header>

      <!-- Resumen principal -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <p class="text-xs font-semibold uppercase tracking-wide" :style="{ color: 'var(--text-secondary)' }">
            Ingresos del mes
          </p>
          <p class="text-3xl font-bold mt-3" :style="{ color: 'var(--color-primary)' }">
            {{ formatCurrency(statsSafe.ingresosMes) }}
          </p>
          <span class="text-sm" :style="{ color: 'var(--text-secondary)' }">
            Citas confirmadas y completadas del periodo actual.
          </span>
        </Card>
        <Card>
          <p class="text-xs font-semibold uppercase tracking-wide" :style="{ color: 'var(--text-secondary)' }">
            Citas para hoy
          </p>
          <p class="text-3xl font-bold mt-3" style="color: #16a34a;">
            {{ statsSafe.citasHoy }}
          </p>
          <span class="text-sm" :style="{ color: 'var(--text-secondary)' }">
            Incluye pendientes y confirmadas con fecha de hoy.
          </span>
        </Card>
        <Card>
          <p class="text-xs font-semibold uppercase tracking-wide" :style="{ color: 'var(--text-secondary)' }">
            Citas pendientes
          </p>
          <p class="text-3xl font-bold mt-3" style="color: #ea580c;">
            {{ statsSafe.citasPendientes }}
          </p>
          <span class="text-sm" :style="{ color: 'var(--text-secondary)' }">
            Citas que necesitan confirmación o seguimiento.
          </span>
        </Card>
      </div>

      <!-- Ventas totales -->
      <Card>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
          <div>
            <h2 class="text-2xl font-bold" :style="{ color: 'var(--text-primary)' }">
              Ventas totales
            </h2>
            <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
              Indicadores clave del desempeño comercial acumulado.
            </p>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="p-4 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)', borderColor: 'var(--border-color)' }">
            <p class="text-xs uppercase font-semibold" :style="{ color: 'var(--text-secondary)' }">
              Total facturado
            </p>
            <p class="text-2xl font-bold mt-2" :style="{ color: 'var(--text-primary)' }">
              {{ formatCurrency(salesSummarySafe.ventasTotales) }}
            </p>
          </div>
          <div class="p-4 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)', borderColor: 'var(--border-color)' }">
            <p class="text-xs uppercase font-semibold" :style="{ color: 'var(--text-secondary)' }">
              Mes actual
            </p>
            <p class="text-2xl font-bold mt-2" :style="{ color: 'var(--text-primary)' }">
              {{ formatCurrency(salesSummarySafe.ventasMesActual) }}
            </p>
          </div>
          <div class="p-4 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)', borderColor: 'var(--border-color)' }">
            <p class="text-xs uppercase font-semibold" :style="{ color: 'var(--text-secondary)' }">
              Ticket promedio
            </p>
            <p class="text-2xl font-bold mt-2" :style="{ color: 'var(--text-primary)' }">
              {{ formatCurrency(salesSummarySafe.ticketPromedio) }}
            </p>
          </div>
          <div class="p-4 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)', borderColor: 'var(--border-color)' }">
            <p class="text-xs uppercase font-semibold" :style="{ color: 'var(--text-secondary)' }">
              Citas cobradas
            </p>
            <p class="text-2xl font-bold mt-2" style="color: #16a34a;">
              {{ salesSummarySafe.citasCobradas }}
            </p>
          </div>
        </div>
      </Card>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-bold" :style="{ color: 'var(--text-primary)' }">
                Ingresos mensuales
              </h3>
              <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
                Últimos 6 meses
              </p>
            </div>
          </div>
          <div class="h-72">
            <BarChart :chartData="ingresosChartData" :chartOptions="chartOptions" />
          </div>
        </Card>
        <Card>
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-bold" :style="{ color: 'var(--text-primary)' }">
                Estado de citas
              </h3>
              <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
                Distribución total
              </p>
            </div>
          </div>
          <div class="h-72 flex items-center justify-center">
            <DoughnutChart :chartData="estadosChartData" :chartOptions="doughnutOptions" />
          </div>
        </Card>
      </div>

      <!-- Ventas por producto y citas por barbero -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Ventas por producto
          </h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr :style="{ color: 'var(--text-secondary)' }" class="text-left">
                  <th class="py-2 pr-4 font-semibold">Producto</th>
                  <th class="py-2 pr-4 font-semibold">Cantidad</th>
                  <th class="py-2 pr-4 font-semibold">Ingresos</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(producto, index) in productSalesSafe"
                  :key="index"
                  class="border-t"
                  :style="{ borderColor: 'var(--border-color)' }"
                >
                  <td class="py-3 pr-4 font-medium" :style="{ color: 'var(--text-primary)' }">
                    {{ producto.producto }}
                  </td>
                  <td class="py-3 pr-4 font-semibold" :style="{ color: 'var(--text-secondary)' }">
                    {{ producto.cantidad }}
                  </td>
                  <td class="py-3 pr-4 font-semibold" :style="{ color: 'var(--color-primary)' }">
                    {{ formatCurrency(producto.ingresos) }}
                  </td>
                </tr>
                <tr v-if="productSalesSafe.length === 0">
                  <td colspan="3" class="py-4 text-center" :style="{ color: 'var(--text-secondary)' }">
                    No hay movimientos de salida registrados.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Card>

        <Card>
          <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Citas por barbero
          </h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr :style="{ color: 'var(--text-secondary)' }" class="text-left">
                  <th class="py-2 pr-4 font-semibold">Barbero</th>
                  <th class="py-2 pr-4 font-semibold">Citas</th>
                  <th class="py-2 pr-4 font-semibold">Completadas</th>
                  <th class="py-2 pr-4 font-semibold">Ingresos</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(barbero, index) in barberStatsSafe"
                  :key="index"
                  class="border-t"
                  :style="{ borderColor: 'var(--border-color)' }"
                >
                  <td class="py-3 pr-4 font-medium" :style="{ color: 'var(--text-primary)' }">
                    {{ barbero.barbero }}
                  </td>
                  <td class="py-3 pr-4 font-semibold" :style="{ color: 'var(--text-secondary)' }">
                    {{ barbero.total_citas }}
                  </td>
                  <td class="py-3 pr-4 font-semibold" style="color: #16a34a;">
                    {{ barbero.completadas }}
                  </td>
                  <td class="py-3 pr-4 font-semibold" :style="{ color: 'var(--color-primary)' }">
                    {{ formatCurrency(barbero.ingresos) }}
                  </td>
                </tr>
                <tr v-if="barberStatsSafe.length === 0">
                  <td colspan="4" class="py-4 text-center" :style="{ color: 'var(--text-secondary)' }">
                    No existen citas registradas.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Card>
      </div>

      <!-- Detalle de ventas e inventario -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Detalle de ventas recientes
          </h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr :style="{ color: 'var(--text-secondary)' }" class="text-left">
                  <th class="py-2 pr-4 font-semibold">Cliente</th>
                  <th class="py-2 pr-4 font-semibold">Barbero</th>
                  <th class="py-2 pr-4 font-semibold">Servicios</th>
                  <th class="py-2 pr-4 font-semibold">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="venta in recentSalesSafe"
                  :key="venta.id"
                  class="border-t"
                  :style="{ borderColor: 'var(--border-color)' }"
                >
                  <td class="py-3 pr-4">
                    <p class="font-semibold" :style="{ color: 'var(--text-primary)' }">
                      {{ venta.cliente }}
                    </p>
                    <span class="text-xs" :style="{ color: 'var(--text-secondary)' }">
                      {{ venta.fecha }} · {{ venta.estado }}
                    </span>
                  </td>
                  <td class="py-3 pr-4" :style="{ color: 'var(--text-secondary)' }">
                    {{ venta.barbero }}
                  </td>
                  <td class="py-3 pr-4" :style="{ color: 'var(--text-secondary)' }">
                    {{ venta.servicios.join(', ') || 'N/D' }}
                  </td>
                  <td class="py-3 pr-4 font-semibold" :style="{ color: 'var(--color-primary)' }">
                    {{ formatCurrency(venta.monto) }}
                  </td>
                </tr>
                <tr v-if="recentSalesSafe.length === 0">
                  <td colspan="4" class="py-4 text-center" :style="{ color: 'var(--text-secondary)' }">
                    No hay ventas confirmadas o completadas.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Card>

        <Card>
          <div class="flex flex-col gap-4 mb-4">
            <div>
              <h3 class="text-lg font-bold" :style="{ color: 'var(--text-primary)' }">
                Movimiento de inventario
              </h3>
              <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
                Entradas, salidas y ajustes recientes.
              </p>
            </div>
            <div class="grid grid-cols-3 gap-3">
              <div class="p-3 rounded-lg" style="background-color: #ecfccb;">
                <p class="text-xs font-semibold uppercase" style="color: #4d7c0f;">
                  Entradas
                </p>
                <p class="text-2xl font-bold text-lime-900">
                  {{ inventorySummarySafe.entradas }}
                </p>
              </div>
              <div class="p-3 rounded-lg" style="background-color: #fee2e2;">
                <p class="text-xs font-semibold uppercase" style="color: #b91c1c;">
                  Salidas
                </p>
                <p class="text-2xl font-bold text-rose-900">
                  {{ inventorySummarySafe.salidas }}
                </p>
              </div>
              <div class="p-3 rounded-lg" style="background-color: #e0f2fe;">
                <p class="text-xs font-semibold uppercase" style="color: #0369a1;">
                  Ajustes
                </p>
                <p class="text-2xl font-bold text-sky-900">
                  {{ inventorySummarySafe.ajustes }}
                </p>
              </div>
            </div>
          </div>
          <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
            <div
              v-for="movimiento in inventoryMovementsSafe"
              :key="movimiento.id"
              class="border p-3 rounded-lg"
              :style="{ borderColor: 'var(--border-color)' }"
            >
              <div class="flex items-center justify-between">
                <p class="font-semibold" :style="{ color: 'var(--text-primary)' }">
                  {{ movimiento.producto }}
                </p>
                <span
                  class="text-xs font-semibold uppercase px-2 py-1 rounded-full"
                  :style="badgeStyle(movimiento.tipo)"
                >
                  {{ movimiento.tipo }}
                </span>
              </div>
              <p class="text-sm mt-1" :style="{ color: 'var(--text-secondary)' }">
                {{ movimiento.motivo }}
              </p>
              <div class="flex items-center justify-between text-xs mt-2" :style="{ color: 'var(--text-secondary)' }">
                <span>Cantidad: {{ movimiento.cantidad }}</span>
                <span>{{ movimiento.fecha }}</span>
              </div>
            </div>
            <p v-if="inventoryMovementsSafe.length === 0" class="text-center text-sm" :style="{ color: 'var(--text-secondary)' }">
              No se registran movimientos todavía.
            </p>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue';

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  salesSummary: { type: Object, default: () => ({}) },
  charts: { type: Object, default: () => ({}) },
  productSales: { type: Array, default: () => [] },
  barberStats: { type: Array, default: () => [] },
  recentSales: { type: Array, default: () => [] },
  inventoryMovements: { type: Array, default: () => [] },
  inventorySummary: { type: Object, default: () => ({}) },
});

const statsSafe = computed(() => ({
  ingresosMes: props.stats?.ingresosMes ?? 0,
  citasHoy: props.stats?.citasHoy ?? 0,
  citasPendientes: props.stats?.citasPendientes ?? 0,
}));

const salesSummarySafe = computed(() => ({
  ventasTotales: props.salesSummary?.ventasTotales ?? 0,
  ventasMesActual: props.salesSummary?.ventasMesActual ?? 0,
  ticketPromedio: props.salesSummary?.ticketPromedio ?? 0,
  citasCobradas: props.salesSummary?.citasCobradas ?? 0,
}));

const productSalesSafe = computed(() => props.productSales ?? []);
const barberStatsSafe = computed(() => props.barberStats ?? []);
const recentSalesSafe = computed(() => props.recentSales ?? []);
const inventoryMovementsSafe = computed(() => props.inventoryMovements ?? []);
const inventorySummarySafe = computed(() => ({
  entradas: props.inventorySummary?.entradas ?? 0,
  salidas: props.inventorySummary?.salidas ?? 0,
  ajustes: props.inventorySummary?.ajustes ?? 0,
}));

const currencyFormatter = new Intl.NumberFormat('es-BO', {
  style: 'currency',
  currency: 'BOB',
  minimumFractionDigits: 2,
});

const formatCurrency = (value) => currencyFormatter.format(value ?? 0);

const ingresosChartData = computed(() => ({
  labels: props.charts?.ingresos?.labels ?? [],
  datasets: [
    {
      label: 'Ingresos (Bs.)',
      backgroundColor: '#4F46E5',
      borderRadius: 6,
      data: props.charts?.ingresos?.data ?? [],
    },
  ],
}));

const estadosChartData = computed(() => ({
  labels: props.charts?.estados?.labels ?? [],
  datasets: [
    {
      backgroundColor: ['#F59E0B', '#10B981', '#EF4444', '#3B82F6'],
      borderColor: '#ffffff',
      borderWidth: 2,
      data: props.charts?.estados?.data ?? [],
    },
  ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (context) => `${formatCurrency(context.parsed.y)}`,
      },
    },
  },
  scales: {
    x: { grid: { display: false } },
    y: {
      ticks: {
        callback: (value) => formatCurrency(value),
      },
    },
  },
};

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom' },
  },
};

const badgeStyle = (tipo) => {
  const styles = {
    Entrada: { backgroundColor: '#d9f99d', color: '#3f6212' },
    Salida: { backgroundColor: '#fecaca', color: '#b91c1c' },
    Ajuste: { backgroundColor: '#bae6fd', color: '#0369a1' },
  };
  return styles[tipo] || { backgroundColor: 'var(--bg-secondary)', color: 'var(--text-secondary)' };
};
</script>


<template>
  <ChartWrapper 
    title="Movimientos de Inventario" 
    subtitle="Entradas, Salidas y Ajustes (30 días)"
    :endpoint="route('bi.inventario')"
    v-slot="{ data }"
  >
    <apexchart 
      type="bar" 
      height="300" 
      :options="chartOptions" 
      :series="getSeries(data)" 
    />
  </ChartWrapper>
</template>

<script setup>
import ChartWrapper from './ChartWrapper.vue';

const chartOptions = {
  chart: { id: 'inventario-chart' },
  plotOptions: { bar: { distributed: true, borderRadius: 4 } },
  colors: ['#10b981', '#ef4444', '#f59e0b'],
  xaxis: {
    categories: [],
    labels: { style: { colors: 'var(--text-secondary)' } }
  },
  legend: { show: false },
};

const getSeries = (data) => {
  if (!data) return [];
  chartOptions.xaxis.categories = data.map(item => item.name);
  return [{ name: 'Movimientos', data: data.map(item => item.total) }];
};
</script>

<template>
  <ChartWrapper 
    title="Distribución de Ingresos" 
    subtitle="Balance entre Productos y Servicios"
    :endpoint="route('bi.distribucion')"
    v-slot="{ data }"
  >
    <apexchart 
      type="pie" 
      height="300" 
      :options="chartOptions" 
      :series="getSeries(data)" 
    />
  </ChartWrapper>
</template>

<script setup>
import ChartWrapper from './ChartWrapper.vue';

const chartOptions = {
  chart: { id: 'distribucion-chart' },
  labels: [],
  colors: ['#6366f1', '#f43f5e'],
  legend: { position: 'bottom', labels: { colors: 'var(--text-primary)' } },
  dataLabels: {
    formatter: (val, opts) => `${opts.w.config.series[opts.seriesIndex]}%`
  }
};

const getSeries = (data) => {
  if (!data) return [];
  chartOptions.labels = data.map(item => item.name);
  return data.map(item => item.total);
};
</script>

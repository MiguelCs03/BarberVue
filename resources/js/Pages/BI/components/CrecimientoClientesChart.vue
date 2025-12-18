<template>
  <ChartWrapper 
    title="Crecimiento de Clientes" 
    subtitle="Nuevos registros mensuales"
    :endpoint="route('bi.clientes')"
    v-slot="{ data }"
  >
    <apexchart 
      type="area" 
      height="300" 
      :options="chartOptions" 
      :series="getSeries(data)" 
    />
  </ChartWrapper>
</template>

<script setup>
import ChartWrapper from './ChartWrapper.vue';

const chartOptions = {
  chart: { id: 'clientes-chart', toolbar: { show: false } },
  stroke: { curve: 'smooth' },
  fill: {
    type: 'gradient',
    gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3 }
  },
  xaxis: {
    categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    labels: { style: { colors: 'var(--text-secondary)' } }
  },
  colors: ['#ec4899'],
};

const getSeries = (data) => {
  if (!data) return [];
  const fullYear = Array(12).fill(0);
  data.forEach(item => {
    const monthIndex = parseInt(item.month) - 1;
    if(monthIndex >= 0 && monthIndex < 12) fullYear[monthIndex] = item.total;
  });
  return [{ name: 'Nuevos Clientes', data: fullYear }];
};
</script>

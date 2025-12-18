<template>
  <ChartWrapper 
    title="Ingresos Mensuales" 
    subtitle="Evolución de ventas en el último año"
    :endpoint="route('bi.ventas')"
    v-slot="{ data }"
  >
    <apexchart 
      type="line" 
      height="300" 
      :options="chartOptions" 
      :series="getSeries(data)" 
    />
  </ChartWrapper>
</template>

<script setup>
import ChartWrapper from './ChartWrapper.vue';

const chartOptions = {
  chart: {
    id: 'ventas-mensuales-chart',
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif',
  },
  stroke: { curve: 'smooth', width: 3 },
  colors: ['#3b82f6'],
  xaxis: {
    categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    labels: { style: { colors: 'var(--text-secondary)' } }
  },
  yaxis: {
    labels: { 
      formatter: (val) => `$${val.toFixed(0)}`,
      style: { colors: 'var(--text-secondary)' } 
    }
  },
  grid: { borderColor: 'var(--border-color)' },
  markers: { size: 4 },
  dataLabels: { enabled: false },
  tooltip: { theme: 'dark' }
};

const getSeries = (data) => {
  if (!data) return [];
  // Mapear meses de SQLite a valores (rellenando ceros si falta data)
  const fullYear = Array(12).fill(0);
  data.forEach(item => {
    const monthIndex = parseInt(item.month) - 1;
    if(monthIndex >= 0 && monthIndex < 12) fullYear[monthIndex] = parseFloat(item.total);
  });

  return [{
    name: 'Ingresos',
    data: fullYear
  }];
};
</script>

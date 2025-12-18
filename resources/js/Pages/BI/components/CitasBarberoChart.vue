<template>
  <ChartWrapper 
    title="Barberos más Cotizados" 
    subtitle="Volumen total de citas por profesional"
    :endpoint="route('bi.barberos')"
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
import { computed } from 'vue';

const chartOptions = {
  chart: {
    id: 'barberos-chart',
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif',
  },
  plotOptions: {
    bar: {
      borderRadius: 6,
      distributed: true,
      horizontal: true,
    }
  },
  colors: ['#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899'],
  xaxis: {
    categories: [],
    labels: { style: { colors: 'var(--text-secondary)' } }
  },
  yaxis: {
    labels: { style: { colors: 'var(--text-secondary)' } }
  },
  legend: { show: false },
  grid: {
    borderColor: 'var(--border-color)',
    strokeDashArray: 4,
  },
  dataLabels: {
    enabled: true,
    style: { fontSize: '12px', colors: ['#fff'] }
  },
  theme: { mode: 'light' } // Se ajustará dinámicamente si es necesario
};

const getSeries = (data) => {
  if (!data) return [];
  
  // Actualizar categorías del eje X (nombre de barberos)
  const names = data.map(item => item.name);
  chartOptions.xaxis.categories = names;

  return [{
    name: 'Citas',
    data: data.map(item => item.total)
  }];
};
</script>

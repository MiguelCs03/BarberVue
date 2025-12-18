<template>
  <ChartWrapper 
    title="Estado de Citas" 
    subtitle="Desglose por situación actual"
    :endpoint="route('bi.estados')"
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
  chart: { id: 'estados-citas-chart' },
  labels: [],
  colors: ['#f59e0b', '#10b981', '#ef4444', '#6b7280'],
  legend: { position: 'bottom', labels: { colors: 'var(--text-primary)' } }
};

const getSeries = (data) => {
  if (!data) return [];
  chartOptions.labels = data.map(item => item.name);
  return data.map(item => item.total);
};
</script>

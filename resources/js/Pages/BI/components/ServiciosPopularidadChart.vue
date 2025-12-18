<template>
  <ChartWrapper 
    title="Servicios Populares" 
    subtitle="Top 10 servicios más solicitados"
    :endpoint="route('bi.servicios')"
    v-slot="{ data }"
  >
    <apexchart 
      type="donut" 
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
    id: 'servicios-chart',
    fontFamily: 'Inter, sans-serif',
  },
  labels: [],
  colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#6366f1'],
  legend: {
    position: 'bottom',
    labels: { colors: 'var(--text-primary)' }
  },
  stroke: { show: false },
  plotOptions: {
    pie: {
      donut: {
        size: '70%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            color: 'var(--text-secondary)'
          }
        }
      }
    }
  },
  dataLabels: { enabled: false }
};

const getSeries = (data) => {
  if (!data) return [];
  chartOptions.labels = data.map(item => item.name);
  return data.map(item => item.total);
};
</script>

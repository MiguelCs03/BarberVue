<template>
  <ChartWrapper 
    title="Alertas de Stock" 
    subtitle="Productos cerca del límite mínimo"
    :endpoint="route('bi.stock')"
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
  chart: { stacked: true, toolbar: { show: false } },
  plotOptions: { bar: { horizontal: true } },
  colors: ['#ef4444', '#e2e8f0'],
  xaxis: {
    categories: [],
    labels: { style: { colors: 'var(--text-secondary)' } }
  },
  yaxis: { labels: { style: { colors: 'var(--text-secondary)' } } },
  legend: { show: true, position: 'top', labels: { colors: 'var(--text-primary)' } },
};

const getSeries = (data) => {
  if (!data) return [];
  chartOptions.xaxis.categories = data.map(item => item.name);
  return [
    { name: 'Stock Actual', data: data.map(item => item.stock) },
    { name: 'Déficit', data: data.map(item => item.deficit) }
  ];
};
</script>

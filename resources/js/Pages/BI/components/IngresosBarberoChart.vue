<template>
  <ChartWrapper 
    title="Ingresos por Profesional" 
    subtitle="Recaudación real por barbero"
    :endpoint="route('bi.ingresos-barbero')"
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
  chart: { toolbar: { show: false } },
  plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
  colors: ['#8b5cf6'],
  xaxis: {
    categories: [],
    labels: { style: { colors: 'var(--text-secondary)' } }
  },
  yaxis: { labels: { style: { colors: 'var(--text-secondary)' } } },
  dataLabels: { enabled: false },
};

const getSeries = (data) => {
  if (!data) return [];
  chartOptions.xaxis.categories = data.map(item => item.name);
  return [{ name: 'Ingresos', data: data.map(item => item.total) }];
};
</script>

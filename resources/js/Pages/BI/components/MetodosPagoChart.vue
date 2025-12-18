<template>
  <ChartWrapper 
    title="Métodos de Pago" 
    subtitle="Preferencia de los clientes"
    :endpoint="route('bi.pagos')"
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
  chart: { id: 'pagos-chart', toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 4, horizontal: true } },
  colors: ['#6366f1'],
  xaxis: { labels: { style: { colors: 'var(--text-secondary)' } } },
  yaxis: { labels: { style: { colors: 'var(--text-secondary)' } } },
};

const getSeries = (data) => {
  if (!data) return [];
  chartOptions.xaxis.categories = data.map(item => item.name);
  return [{ name: 'Transacciones', data: data.map(item => item.total) }];
};
</script>

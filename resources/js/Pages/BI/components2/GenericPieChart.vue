<template>
  <BaseChart 
    :title="title" 
    :subtitle="subtitle" 
    :endpoint="endpoint" 
    :params="params"
    v-slot="{ data }"
  >
    <apexchart 
      v-if="data"
      type="donut" 
      height="350" 
      :options="getOptions(data)" 
      :series="getSeries(data)" 
    />
  </BaseChart>
</template>

<script setup>
import BaseChart from './BaseChart.vue';

const props = defineProps({
  title: String,
  subtitle: String,
  endpoint: String,
  params: Object
});

const getOptions = (data) => {
  const labels = data.map(item => item.name || item.label);
  
  return {
    chart: {
      toolbar: { 
        show: true,
        tools: {
          download: true,
          selection: false,
          zoom: false,
          zoomin: false,
          zoomout: false,
          pan: false,
          reset: false
        }
      },
      fontFamily: 'Outfit, sans-serif',
      animations: {
        enabled: true,
        speed: 800,
        dynamicAnimation: { enabled: true, speed: 450 }
      }
    },
    labels: labels,
    colors: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'],
    plotOptions: {
      pie: {
        donut: {
          size: '72%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Total',
              fontSize: '12px',
              color: 'var(--text-secondary)',
              formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
            }
          }
        }
      }
    },
    dataLabels: { enabled: false },
    legend: {
      show: true,
      position: 'bottom',
      labels: { colors: 'var(--text-primary)' },
      fontFamily: 'Outfit',
      onItemClick: { toggleDataSeries: true }
    },
    tooltip: { theme: 'dark' }
  };
};

const getSeries = (data) => {
  if (!data || data.length === 0) return [];
  return data.map(item => Number(item.total ?? item.value ?? item.cantidad ?? item.deficit ?? 0));
};
</script>

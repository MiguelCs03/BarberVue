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
      type="area" 
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
  params: Object,
  valueName: { type: String, default: 'Cantidad' }
});

const getOptions = (data) => {
  const isMultiSeries = data.length > 0 && Array.isArray(data[0].series);
  const categories = isMultiSeries ? data[0].categories : data.map(item => item.name || item.label);

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
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 800,
        dynamicAnimation: { enabled: true, speed: 450 }
      },
      fontFamily: 'Outfit, sans-serif'
    },
    stroke: { curve: 'smooth', width: 4 },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.45,
        opacityTo: 0.05,
        stops: [20, 100]
      }
    },
    colors: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
    markers: {
      size: 4,
      colors: ['#fff'],
      strokeColors: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
      strokeWidth: 3,
      hover: { size: 7 }
    },
    xaxis: {
      categories: categories,
      labels: { style: { colors: 'var(--text-secondary)', fontSize: '11px' } },
      axisBorder: { show: false },
      axisTicks: { show: false }
    },
    yaxis: {
      labels: { style: { colors: 'var(--text-secondary)', fontSize: '11px' } }
    },
    legend: {
      show: true,
      position: 'bottom',
      labels: { colors: 'var(--text-primary)' },
      fontFamily: 'Outfit',
      onItemClick: { toggleDataSeries: true }
    },
    grid: { borderColor: 'var(--border-color)', strokeDashArray: 4 },
    tooltip: { theme: 'dark' }
  };
};

const getSeries = (data) => {
  if (!data || data.length === 0) return [{ name: props.valueName, data: [] }];
  if (Array.isArray(data[0].series)) return data[0].series;
  
  const plotData = data.map(item => Number(item.total ?? item.value ?? item.cantidad ?? item.deficit ?? 0));
  return [{ name: props.valueName, data: plotData }];
};
</script>

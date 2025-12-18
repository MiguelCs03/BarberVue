<template>
  <div class="p-6 rounded-2xl shadow-lg border transition-all hover:shadow-xl" 
       :style="{ backgroundColor: 'var(--bg-secondary)', borderColor: 'var(--border-color)' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h3 class="text-xl font-bold tracking-tight" :style="{ color: 'var(--text-primary)' }">
          {{ title }}
        </h3>
        <p v-if="subtitle" class="text-sm opacity-80" :style="{ color: 'var(--text-secondary)' }">
          {{ subtitle }}
        </p>
      </div>

      <!-- Controls -->
      <div class="flex items-center gap-3">
        <!-- Extra Selectors Slot -->
        <slot name="extra-filters"></slot>

        <!-- Time Selector -->
        <select 
          v-model="timeFilter" 
          @change="fetchData"
          class="text-sm rounded-lg border-none focus:ring-2 px-3 py-1.5 cursor-pointer shadow-sm"
          :style="{ backgroundColor: 'var(--bg-primary)', color: 'var(--text-primary)', ringColor: 'var(--color-primary)' }"
        >
          <option value="today">Hoy</option>
          <option value="this_week">Esta Semana</option>
          <option value="this_month">Este Mes</option>
          <option value="this_year">Este Año</option>
        </select>

        <button 
          @click="fetchData" 
          class="p-2 rounded-lg hover:opacity-80 transition-opacity shadow-sm"
          :style="{ backgroundColor: 'var(--bg-primary)' }"
        >
          <ArrowPathIcon :class="{'animate-spin': loading}" class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" />
        </button>
      </div>
    </div>

    <!-- Chart Area -->
    <div class="relative min-h-[350px]">
      <div v-if="loading" class="absolute inset-0 flex flex-col items-center justify-center z-10 rounded-xl"
           :style="{ backgroundColor: 'rgba(var(--bg-secondary-rgb), 0.8)' }">
        <div class="w-12 h-12 border-4 border-t-transparent rounded-full animate-spin mb-3" 
             :style="{ borderColor: 'var(--color-primary)' }"></div>
        <span class="text-sm font-medium animate-pulse" :style="{ color: 'var(--text-secondary)' }">Actualizando analíticas...</span>
      </div>

      <div v-if="error" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 bg-red-50 dark:bg-red-900/10 rounded-xl">
        <ExclamationTriangleIcon class="w-12 h-12 mb-3 text-red-500" />
        <p class="font-bold text-red-600 dark:text-red-400">Error de conexión</p>
        <p class="text-sm mb-4" :style="{ color: 'var(--text-secondary)' }">{{ error }}</p>
        <button @click="fetchData" class="btn-primary py-2 px-4 text-xs">Reintentar</button>
      </div>

      <div v-show="!loading && !error" class="w-full">
        <slot :data="data" :loading="loading"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { ArrowPathIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
  title: String,
  subtitle: String,
  endpoint: String,
  params: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['data-loaded']);

const data = ref(null);
const loading = ref(true);
const error = ref(null);
const timeFilter = ref('this_month');

const fetchData = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get(props.endpoint, {
      params: { filter: timeFilter.value, ...props.params }
    });
    data.value = response.data;
    emit('data-loaded', response.data);
  } catch (err) {
    console.error(`BI Error (${props.title}):`, err);
    error.value = "No se pudieron obtener los datos.";
  } finally {
    setTimeout(() => { loading.value = false; }, 400);
  }
};

onMounted(() => { fetchData(); });
watch(() => props.params, () => fetchData(), { deep: true });

defineExpose({ fetchData });
</script>

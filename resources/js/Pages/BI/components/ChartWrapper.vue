<template>
  <div class="p-4 rounded-xl shadow-sm border transition-all hover:shadow-md" 
       :style="{ backgroundColor: 'var(--bg-secondary)', borderColor: 'var(--border-color)' }">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-lg font-bold" :style="{ color: 'var(--text-primary)' }">{{ title }}</h3>
        <p v-if="subtitle" class="text-xs" :style="{ color: 'var(--text-secondary)' }">{{ subtitle }}</p>
      </div>
      <!-- Selectores Dinámicos -->
      <div class="flex gap-2">
        <slot name="selectors"></slot>
        <button @click="refreshData" 
                class="p-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                title="Actualizar">
          <ArrowPathIcon :class="{'animate-spin': loading}" class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" />
        </button>
      </div>
    </div>

    <div class="relative min-h-[300px] flex items-center justify-center">
      <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-opacity-50 z-10"
           :style="{ backgroundColor: 'var(--bg-secondary)' }">
        <div class="animate-pulse flex flex-col items-center">
          <div class="w-12 h-12 border-4 border-t-transparent rounded-full animate-spin mb-2" 
               :style="{ borderColor: 'var(--color-primary)' }"></div>
          <span class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">Cargando datos...</span>
        </div>
      </div>

      <div v-if="error" class="text-center p-6">
        <ExclamationTriangleIcon class="w-12 h-12 mx-auto mb-2 opacity-50" :style="{ color: 'var(--color-danger)' }" />
        <p class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">{{ error }}</p>
        <button @click="refreshData" class="mt-2 text-xs underline" :style="{ color: 'var(--color-primary)' }">Reintentar</button>
      </div>

      <div v-show="!loading && !error" class="w-full h-full">
        <slot :data="chartData"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ArrowPathIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
  title: String,
  subtitle: String,
  endpoint: String,
});

const emit = defineEmits(['loaded']);

const loading = ref(true);
const error = ref(null);
const chartData = ref(null);

const refreshData = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get(props.endpoint);
    chartData.value = response.data;
    emit('loaded', response.data);
  } catch (err) {
    console.error(`Error loading BI data from ${props.endpoint}:`, err);
    error.value = "No se pudieron cargar los datos analíticos.";
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  refreshData();
});

defineExpose({ refreshData });
</script>

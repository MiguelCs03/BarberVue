<template>
  <div class="flex flex-col sm:flex-row gap-2 items-center">
    <div class="datepicker-container">
      <VueDatePicker
        v-model="startDate"
        :dark="isDark"
        :locale="es" 
        cancelText="Cancelar"
        selectText="Aceptar"
        format="dd-MM-yyyy HH:mm"
        :enable-time-picker="true"
        placeholder="Fecha Inicio"
        @update:model-value="handleStartDate"
        :teleport="true"
        :minutes-grid="true"
        :hours-grid="true"
        :minutes-increment="5"
        no-hours-overlay
        no-minutes-overlay
      >
        <template #input-icon>
          <svg class="w-4 h-4 ml-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </template>
      </VueDatePicker>
    </div>

    <span class="text-gray-400 hidden sm:block">-</span>

    <div class="datepicker-container">
      <VueDatePicker
        v-model="endDate"
        :dark="isDark"
        :locale="es" 
        cancelText="Cancelar"
        selectText="Aceptar"
        format="dd-MM-yyyy HH:mm"
        :enable-time-picker="true"
        placeholder="Fecha Fin"
        @update:model-value="handleEndDate"
        :teleport="true"
        :minutes-grid="true"
        :hours-grid="true"
        :minutes-increment="5"
        no-hours-overlay
        no-minutes-overlay
      >
        <template #input-icon>
          <svg class="w-4 h-4 ml-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </template>
      </VueDatePicker>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { es } from 'date-fns/locale';
import { format, isValid } from 'date-fns';

const props = defineProps({
  desde: String,
  hasta: String
});

const emit = defineEmits(['update:desde', 'update:hasta', 'change']);

const startDate = ref(null);
const endDate = ref(null);
const isDark = ref(false);

const checkTheme = () => {
  isDark.value = document.documentElement.classList.contains('dark');
};

onMounted(() => {
  checkTheme();
  const observer = new MutationObserver(checkTheme);
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

  if (props.desde) startDate.value = new Date(props.desde);
  if (props.hasta) endDate.value = new Date(props.hasta);
});

// Sincronizar hacia afuera cuando cambia el modelo interno
const handleStartDate = (val) => {
  const formatted = val && isValid(val) ? format(val, 'yyyy-MM-dd HH:mm:ss') : '';
  emit('update:desde', formatted);
  emit('change');
};

const handleEndDate = (val) => {
  const formatted = val && isValid(val) ? format(val, 'yyyy-MM-dd HH:mm:ss') : '';
  emit('update:hasta', formatted);
  emit('change');
};

// Sincronizar desde afuera si cambian las props (ej: al limpiar filtros)
watch(() => props.desde, (newVal) => {
  if (!newVal) startDate.value = null;
  else if (newVal && (!startDate.value || format(startDate.value, 'yyyy-MM-dd HH:mm:ss') !== newVal)) {
    startDate.value = new Date(newVal);
  }
});

watch(() => props.hasta, (newVal) => {
  if (!newVal) endDate.value = null;
  else if (newVal && (!endDate.value || format(endDate.value, 'yyyy-MM-dd HH:mm:ss') !== newVal)) {
    endDate.value = new Date(newVal);
  }
});
</script>

<style>
.datepicker-container {
  width: 200px; /* Ancho más compacto para que quepan dos */
  --dp-border-radius: 8px;
  --dp-font-size: 0.8rem;
  --dp-background-color: var(--bg-secondary, #ffffff);
  --dp-text-color: var(--text-primary, #1f2937);
  --dp-hover-color: var(--bg-primary, #f3f4f6);
  --dp-hover-text-color: var(--text-primary, #111827);
  --dp-column-header-text-color: var(--text-secondary, #6b7280);
  --dp-primary-color: var(--color-primary, #dc2626);
  --dp-border-color: var(--border-color, #e5e7eb);
}

.dp__theme_light, .dp__theme_dark {
  --dp-primary-color: var(--color-primary);
  --dp-background-color: var(--bg-secondary);
  --dp-text-color: var(--text-primary);
  --dp-border-color: var(--border-color);
}
</style>
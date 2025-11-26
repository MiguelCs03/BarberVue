<template>
  <div class="mb-6">
    <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
      Selecciona un Turno
    </h3>
    

    <div class="flex gap-3 justify-center">
      <button
        v-for="turno in turnos"
        :key="turno.value"
        @click="handleSelect(turno)"
        :disabled="disabled || turno.disabled"
        :class="[
          'turno-button relative py-3 px-6 rounded-lg transition-all',
          (disabled || turno.disabled)
            ? 'opacity-30 cursor-not-allowed bg-gray-500'
            : modelValue === turno.value
            ? 'btn-primary shadow-md'
            : 'btn-secondary hover:shadow-sm'
        ]"
      >
        <div class="flex items-center gap-2">
          <component :is="turno.icon" class="w-5 h-5" />
          <div class="text-left">
            <span class="font-medium block">{{ turno.label }}</span>
          </div>
        </div>
        
        <!-- Indicador de turno no disponible -->
        
      </button>
    </div>
    
    <!-- Mensaje informativo -->
    <p 
      v-if="disabled" 
      class="text-sm mt-2 text-center"
      :style="{ color: 'var(--text-secondary)' }"
    >
      Selecciona una fecha válida para ver los turnos disponibles
    </p>
  </div>
</template>

<script setup>
import { computed, h } from 'vue';
import { SunIcon, CloudIcon, MoonIcon } from '@heroicons/vue/24/outline';


const props = defineProps({
  modelValue: {
    type: String,
    default: null
  },
  disabled: {
    type: Boolean,
    default: false
  },
  selectedDate: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['update:modelValue']);



// Verificar disponibilidad de cada turno
const turnos = computed(() => {
  const now = new Date();
  const currentHour = now.getHours();
  
  let isToday = false;
  
  if (props.selectedDate) {
    const [year, month, day] = props.selectedDate.date.split('-').map(Number);
    const selectedDateObj = new Date(year, month - 1, day);
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    isToday = selectedDateObj.getTime() === today.getTime();
  }
  
  return [
    {
      value: 'manana',
      label: 'Mañana',
      horario: '9:00 - 12:00',
      icon: SunIcon,
      disabled: isToday && currentHour >= 12
    },
    {
      value: 'tarde',
      label: 'Tarde',
      horario: '14:00 - 18:00',
      icon: CloudIcon,
      disabled: isToday && currentHour >= 18
    },
    {
      value: 'noche',
      label: 'Noche',
      horario: '18:00 - 21:00',
      icon: MoonIcon,
      disabled: isToday && currentHour >= 21
    }
  ];
});

const handleSelect = (turno) => {
  if (props.disabled || turno.disabled) return;
  
  if (props.modelValue === turno.value) {
    emit('update:modelValue', null);
    return;
  }
  
  emit('update:modelValue', turno.value);
};
</script>

<style scoped>
.turno-button {
  min-width: 160px;
}
</style>
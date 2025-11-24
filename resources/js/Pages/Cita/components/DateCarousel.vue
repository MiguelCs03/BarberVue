<template>
    <div class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
          {{ currentMonthYear }}
        </h2>
        <div class="flex gap-2">
          <button
            @click="$emit('previous-week')"
            class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :style="{ color: 'var(--text-primary)' }"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            @click="$emit('next-week')"
            class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :style="{ color: 'var(--text-primary)' }"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
  
      <!-- Carrusel de Días -->
      <div class="flex justify-center">
        <div ref="daysContainer" class="flex gap-3 overflow-x-auto pb-2 hide-scrollbar">
          <button
            v-for="day in visibleDays"
            :key="day.date"
            @click="handleSelectDate(day)"
            :disabled="!day.available"
            :class="[
              'flex-shrink-0 w-20 py-3 px-2 rounded-lg text-center transition-all',
              modelValue?.date === day.date
                ? 'btn-primary'
                : day.available
                ? 'border-2 hover:border-opacity-50'
                : 'opacity-40 cursor-not-allowed'
            ]"
            :style="day.available && modelValue?.date !== day.date ? { 
              borderColor: 'var(--color-primary)', 
              color: 'var(--text-primary)' 
            } : {}"
          >
            <div class="text-xs font-medium mb-1">
              {{ day.dayName }}
            </div>
            <div class="text-2xl font-bold">
              {{ day.dayNumber }}
            </div>
            <div v-if="day.available" class="w-2 h-2 rounded-full mx-auto mt-2" 
                 :style="{ backgroundColor: modelValue?.date === day.date ? 'white' : 'var(--color-primary)' }">
            </div>
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, nextTick, watch } from 'vue';
  
  const props = defineProps({
    modelValue: {
      type: Object,
      default: null
    },
    currentWeekStart: {
      type: Date,
      required: true
    }
  });
  
  const emit = defineEmits(['update:modelValue', 'previous-week', 'next-week']);
  
  const daysContainer = ref(null);
  
  const currentMonthYear = computed(() => {
    const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return `${months[props.currentWeekStart.getMonth()]} ${props.currentWeekStart.getFullYear()}`;
  });
  
  const visibleDays = computed(() => {
    const days = [];
    const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    
    for (let i = 0; i < 7; i++) {
      const date = new Date(props.currentWeekStart);
      date.setDate(date.getDate() + i);
      
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const available = date >= today;
      
      days.push({
        date: date.toISOString().split('T')[0],
        dayName: dayNames[date.getDay()],
        dayNumber: date.getDate(),
        fullDate: date,
        available
      });
    }
    return days;
  });
  
  const handleSelectDate = (day) => {
    if (!day.available) return;
    
    // Si clickea la misma fecha, la deselecciona
    if (props.modelValue?.date === day.date) {
      emit('update:modelValue', null);
      return;
    }
    
    emit('update:modelValue', day);
    
    // Centrar el día seleccionado
    nextTick(() => {
      if (daysContainer.value) {
        const dayButtons = daysContainer.value.querySelectorAll('button');
        const selectedIndex = visibleDays.value.findIndex(d => d.date === day.date);
        if (dayButtons[selectedIndex]) {
          dayButtons[selectedIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
      }
    });
  };
  
  // Centrar el primer día disponible al montar
  watch(() => props.currentWeekStart, () => {
    nextTick(() => {
      if (daysContainer.value) {
        const firstAvailableDay = visibleDays.value.find(d => d.available);
        if (firstAvailableDay) {
          const dayButtons = daysContainer.value.querySelectorAll('button');
          const index = visibleDays.value.findIndex(d => d.date === firstAvailableDay.date);
          if (dayButtons[index]) {
            dayButtons[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
          }
        }
      }
    });
  }, { immediate: true });
  </script>
  
  <style scoped>
  .hide-scrollbar {
    scrollbar-width: none;
    -ms-overflow-style: none;
  }
  
  .hide-scrollbar::-webkit-scrollbar {
    display: none;
  }
  </style>
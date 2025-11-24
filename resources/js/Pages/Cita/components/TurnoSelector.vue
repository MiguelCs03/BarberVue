<template>
    <div class="mb-6">
      <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
        Turno
      </h3>
      <div class="flex gap-2">
        <button
          v-for="turno in turnos"
          :key="turno.value"
          @click="handleSelect(turno.value)"
          :class="[
            'flex-1 py-2 px-4 rounded-lg transition-all',
            modelValue === turno.value ? 'btn-primary' : 'btn-secondary'
          ]"
        >
          {{ turno.label }}
        </button>
      </div>
    </div>
  </template>
  
  <script setup>
  const props = defineProps({
    modelValue: {
      type: String,
      default: null
    },
    turnos: {
      type: Array,
      default: () => [
        { value: 'manana', label: 'Mañana' },
        { value: 'tarde', label: 'Tarde' },
        { value: 'noche', label: 'Noche' },
      ]
    }
  });
  
  const emit = defineEmits(['update:modelValue']);
  
  const handleSelect = (turno) => {
    // Si clickea el mismo turno, lo deselecciona
    if (props.modelValue === turno) {
      emit('update:modelValue', null);
      return;
    }
    
    emit('update:modelValue', turno);
  };
  </script>
<template>
    <div class="mb-6">
      <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
        Servicios Disponibles
      </h3>
  
      <div class="space-y-2">
        <button
          v-for="servicio in servicios"
          :key="servicio.id"
          @click="toggleServicio(servicio)"
          :class="[
            'w-full p-4 rounded-lg border-2 transition-all text-left',
            isServicioSelected(servicio.id) ? 'border-opacity-100' : 'border-opacity-20 hover:border-opacity-40'
          ]"
          :style="{ 
            borderColor: 'var(--color-primary)',
            backgroundColor: isServicioSelected(servicio.id) ? 'var(--color-primary)' : 'transparent',
            color: isServicioSelected(servicio.id) ? 'white' : 'var(--text-primary)'
          }"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <p class="font-semibold mb-1">{{ servicio.nombre }}</p>
              <p class="text-sm opacity-75">{{ servicio.descripcion }}</p>
              <div class="flex items-center gap-4 mt-2 text-sm">
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ servicio.duracion_estimada }} min
                </span>
                <span class="font-bold">Bs. {{ servicio.precio.toFixed(2) }}</span>
              </div>
            </div>
            <div class="ml-4">
              <div :class="[
                'w-6 h-6 rounded border-2 flex items-center justify-center transition-all',
                isServicioSelected(servicio.id) ? 'border-white' : 'border-current'
              ]">
                <svg v-if="isServicioSelected(servicio.id)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
          </div>
        </button>
      </div>
    </div>
  </template>
  
  <script setup>
  const props = defineProps({
    modelValue: {
      type: Array,
      default: () => []
    },
    servicios: {
      type: Array,
      default: () => []
    }
  });
  
  const emit = defineEmits(['update:modelValue']);
  
  const isServicioSelected = (servicioId) => {
    return props.modelValue.some(s => s.id === servicioId);
  };
  
  const toggleServicio = (servicio) => {
    const newValue = [...props.modelValue];
    const index = newValue.findIndex(s => s.id === servicio.id);
    
    if (index > -1) {
      newValue.splice(index, 1);
    } else {
      newValue.push(servicio);
    }
    
    emit('update:modelValue', newValue);
  };
  </script>
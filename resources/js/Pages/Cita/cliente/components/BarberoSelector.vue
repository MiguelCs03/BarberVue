<template>
  <div class="mb-6">
    <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
      Selecciona un Barbero
    </h3>
    
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2" :style="{ borderColor: 'var(--color-primary)' }"></div>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
      
      <button
        v-for="barbero in barberos"
        :key="barbero.id"
        @click="handleSelect(barbero.id)"
        :disabled="!isDisponible(barbero)"
        :class="[
          'p-4 rounded-lg border-2 transition-all text-left',
          !isDisponible(barbero) && 'opacity-50 cursor-not-allowed',
          modelValue === barbero.id ? 'border-opacity-100' : 'border-opacity-20 hover:border-opacity-40'
        ]"
        :style="{ 
          borderColor: 'var(--color-primary)',
          backgroundColor: modelValue === barbero.id ? 'var(--color-primary)' : 'transparent',
          color: modelValue === barbero.id ? 'white' : 'var(--text-primary)'
        }"
      >
        <div class="flex items-center gap-3">
          <div class="relative">
            <img 
              v-if="barbero.url_profile"
              :src="barbero.url_profile" 
              :alt="barbero.name"
              class="w-12 h-12 rounded-full object-cover"
            />
            
            <div 
              v-else
              class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg"
              :style="{ 
                backgroundColor: modelValue === barbero.id ? 'rgba(255,255,255,0.3)' : 'var(--color-primary)'
              }"
            >
              {{ getInitial(barbero.name) }}
            </div>

            <div 
              v-if="!isDisponible(barbero)" 
              class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full"
            >
              <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
          </div>
          
          <div>
            <p class="font-semibold">{{ barbero.name }}</p>
            <p class="text-xs opacity-75">
              {{ isDisponible(barbero) ? `${barbero.servicios.length} servicios disponibles` : getEstadoLabel(barbero.estado_barbero) }}
            </p>
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Number, // Ahora esperamos un ID o null, pero preferentemente un ID
    default: null
  },
  barberos: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const getInitial = (nombre) => {
  if (!nombre) return '?';
  return nombre.charAt(0).toUpperCase();
};

const isDisponible = (barbero) => {
  return barbero.estado_barbero === 'disponible';
};

const getEstadoLabel = (estado) => {
  const estados = {
    'disponible': 'Disponible',
    'ocupado': 'Ocupado',
    'descanso': 'En descanso'
  };
  return estados[estado] || 'No disponible';
};

const handleSelect = (barberoId) => {
  const barbero = props.barberos.find(b => b.id === barberoId);
  
  // Validar disponibilidad
  if (!barbero || !isDisponible(barbero)) return;
  
  // Permitir deseleccionar (volver a null) si hace click en el mismo
  if (props.modelValue === barberoId) {
    emit('update:modelValue', null);
    return;
  }
  
  emit('update:modelValue', barberoId);
};
</script>
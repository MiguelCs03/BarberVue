<template>
  <div class="mb-6">
    <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
      Selecciona un Barbero
    </h3>
    <p class="text-sm mb-4" :style="{ color: 'var(--text-secondary)' }">
      Si no seleccionas un barbero, se mostrarán todos los servicios disponibles
    </p>

    <!-- Loading de barberos -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2" :style="{ borderColor: 'var(--color-primary)' }"></div>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
      <!-- Opción: Cualquier barbero -->
      <button
        @click="handleSelect(null)"
        :class="[
          'p-4 rounded-lg border-2 transition-all text-left',
          modelValue === null ? 'border-opacity-100' : 'border-opacity-20 hover:border-opacity-40'
        ]"
        :style="{ 
          borderColor: 'var(--color-primary)',
          backgroundColor: modelValue === null ? 'var(--color-primary)' : 'transparent',
          color: modelValue === null ? 'white' : 'var(--text-primary)'
        }"
      >
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full flex items-center justify-center" 
               :style="{ backgroundColor: modelValue === null ? 'rgba(255,255,255,0.2)' : 'var(--color-primary)', opacity: modelValue === null ? 1 : 0.2 }">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div>
            <p class="font-semibold">Cualquier barbero</p>
            <p class="text-xs opacity-75">Todos los servicios</p>
          </div>
        </div>
      </button>

      <!-- Lista de barberos -->
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
            <!-- Avatar con imagen -->
            <img 
              v-if="barbero.url_profile"
              :src="barbero.url_profile" 
              :alt="barbero.name"
              class="w-12 h-12 rounded-full object-cover"
            />
            
            <!-- Avatar con inicial -->
            <div 
              v-else
              class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg"
              :style="{ 
                backgroundColor: modelValue === barbero.id ? 'rgba(255,255,255,0.3)' : 'var(--color-primary)'
              }"
            >
              {{ getInitial(barbero.name) }}
            </div>

            <!-- Overlay de no disponible -->
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
              Brinda {{ isDisponible(barbero) ? `${barbero.servicios.length} servicios` : getEstadoLabel(barbero.estado_barbero) }}
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
    type: Number,
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

/**
 * Obtiene la primera letra del nombre en mayúscula
 */
const getInitial = (nombre) => {
  if (!nombre) return '?';
  return nombre.charAt(0).toUpperCase();
};

/**
 * Verifica si el barbero está disponible según su estado
 */
const isDisponible = (barbero) => {
  return barbero.estado_barbero === 'disponible';
};

/**
 * Obtiene la etiqueta amigable para mostrar según el estado
 */
const getEstadoLabel = (estado) => {
  const estados = {
    'disponible': 'Disponible',
    'ocupado': 'Ocupado',
    'descanso': 'En descanso'
  };
  return estados[estado] || 'No disponible';
};

const handleSelect = (barberoId) => {
  // Si el barbero no está disponible, no hacer nada
  if (barberoId !== null) {
    const barbero = props.barberos.find(b => b.id === barberoId);
    if (!barbero || !isDisponible(barbero)) return;
  }
  
  // Si clickea el mismo barbero, lo deselecciona (vuelve a "Cualquier barbero")
  if (props.modelValue === barberoId) {
    emit('update:modelValue', null);
    return;
  }
  
  emit('update:modelValue', barberoId);
};
</script>
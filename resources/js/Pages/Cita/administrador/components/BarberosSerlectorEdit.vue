<template>
    <div class="mb-6">
      <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
        Selecciona un Barbero
      </h3>
      
      <div v-if="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2" :style="{ borderColor: 'var(--color-primary)' }"></div>
      </div>
  
      <div v-else-if="barberos.length === 0" class="text-center py-8">
        <svg class="w-16 h-16 mx-auto mb-3 opacity-50" :style="{ color: 'var(--text-secondary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
        </svg>
        <p class="font-semibold mb-1" :style="{ color: 'var(--text-primary)' }">
          No hay barberos disponibles
        </p>
        <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
          Intenta con otra fecha u hora
        </p>
      </div>
  
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
        
        <button
          v-for="barbero in barberos"
          :key="barbero.id"
          @click="handleSelect(barbero.id)"
          :disabled="!barbero.disponible"
          :class="[
            'p-4 rounded-lg border-2 transition-all text-left',
            !barbero.disponible && 'opacity-50 cursor-not-allowed',
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
                v-if="barbero.avatar"
                :src="barbero.avatar" 
                :alt="barbero.nombre"
                class="w-12 h-12 rounded-full object-cover"
              />
              
              <div 
                v-else
                class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg"
                :style="{ 
                  backgroundColor: modelValue === barbero.id ? 'rgba(255,255,255,0.3)' : 'var(--color-primary)'
                }"
              >
                {{ getInitial(barbero.nombre) }}
              </div>
  
              <div 
                v-if="!barbero.disponible" 
                class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full"
              >
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
            </div>
            
            <div class="flex-1">
              <p class="font-semibold">{{ barbero.nombre }}</p>
              <p class="text-xs opacity-75">
                {{ barbero.disponible ? 'Disponible' : 'No disponible' }}
              </p>
            </div>
  
            <div v-if="modelValue === barbero.id" class="flex-shrink-0">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
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
  
  const getInitial = (nombre) => {
    if (!nombre) return '?';
    return nombre.charAt(0).toUpperCase();
  };
  
  const handleSelect = (barberoId) => {
    // Validación estricta: si llega null (por error) o no existe, salimos
    const barbero = props.barberos.find(b => b.id === barberoId);
    
    if (!barbero || !barbero.disponible) return;
    
    // Si clickea el mismo barbero, permitimos deseleccionar (volver a null)
    // Esto hará que el botón de "Guardar" se deshabilite en el padre
    if (props.modelValue === barberoId) {
      emit('update:modelValue', null);
      return;
    }
    
    emit('update:modelValue', barberoId);
  };
  </script>
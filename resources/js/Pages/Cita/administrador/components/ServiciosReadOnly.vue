<template>
    <div class="mb-6 p-4 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)' }">
      <div class="flex items-center gap-2 mb-3">
        <ScissorsIcon class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" />
        <h3 class="font-semibold" :style="{ color: 'var(--text-primary)' }">
          {{ title }}
        </h3>
      </div>
      
      <div v-if="servicios.length > 0" class="space-y-2">
        <div
          v-for="servicio in servicios"
          :key="servicio.id"
          class="flex justify-between items-center p-3 rounded-lg"
          :style="{ backgroundColor: 'var(--bg-primary)' }"
        >
          <div class="flex-1">
            <span class="font-medium block" :style="{ color: 'var(--text-primary)' }">
              {{ servicio.nombre }}
            </span>
            <span 
              v-if="servicio.descripcion" 
              class="text-sm block mt-1" 
              :style="{ color: 'var(--text-secondary)' }"
            >
              {{ servicio.descripcion }}
            </span>
          </div>
          <span class="font-bold ml-4" :style="{ color: 'var(--color-primary)' }">
            Bs. {{ Number(servicio.precio).toFixed(2) }}
          </span>
        </div>
      </div>
  
      <div v-else class="text-center py-4" :style="{ color: 'var(--text-secondary)' }">
        No hay servicios registrados
      </div>
    </div>
  </template>
  
  <script setup>
  import { ScissorsIcon } from '@heroicons/vue/24/outline';
  
  defineProps({
    servicios: {
      type: Array,
      required: true,
      default: () => []
    },
    title: {
      type: String,
      default: 'Servicios Contratados (No se pueden modificar)'
    }
  });
  </script>
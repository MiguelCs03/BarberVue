<template>
    <div class="border-t pt-6" :style="{ borderColor: 'var(--text-secondary)' }">
      <div class="flex items-center justify-between mb-4">
        <span class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
          Duración Total:
        </span>
        <span class="text-xl font-bold" :style="{ color: 'var(--color-secondary)' }">
          {{ totalDuracion }} min
        </span>
      </div>
      <div class="flex items-center justify-between mb-6">
        <span class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
          Total:
        </span>
        <span class="text-2xl font-bold" :style="{ color: 'var(--color-primary)' }">
          Bs. {{ totalPrecio.toFixed(2) }}
        </span>
      </div>
      
    

      <!-- Pago Inicial (A cuenta) -->
      <div class="flex items-center justify-between mb-6">
        <span class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
          Pago Inicial:
        </span>
        <span class="text-xl font-bold" :style="{ color: 'var(--color-secondary)' }">
          Bs. {{ pagoInicial.toFixed(2) }}
        </span>
      </div>

      <button
        @click="$emit('confirmar')"
        :disabled="!canConfirm"
        :class="[
          'w-full py-3 rounded-lg font-semibold text-lg transition-all',
          canConfirm ? 'btn-primary' : 'opacity-50 cursor-not-allowed btn-secondary'
        ]"
      >
        Confirmar Cita
      </button>

      <!-- Nota informativa -->
      <p class="text-xs text-center mt-3" :style="{ color: 'var(--text-secondary)' }">
        El saldo restante (Bs. {{ (totalPrecio - pagoInicial).toFixed(2) }}) se pagará al finalizar el servicio
      </p>
    </div>
  </template>
  
  <script setup>
  defineProps({
    totalDuracion: {
      type: Number,
      required: true
    },
    totalPrecio: {
      type: Number,
      required: true
    },
    porcentajeReserva: {
      type: Number,
      required: true
    },
    pagoInicial: {
      type: Number,
      required: true
    },
    canConfirm: {
      type: Boolean,
      required: true
    }
  });
  
  defineEmits(['confirmar']);
  </script>
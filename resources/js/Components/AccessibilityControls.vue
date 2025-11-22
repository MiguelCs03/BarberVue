<template>
  <div class="flex items-center gap-3">
    <!-- Font Size Controls -->
    <div class="flex items-center gap-1 px-3 py-1 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)' }">
      <button
        @click="decreaseFontSize"
        :disabled="!canDecreaseFontSize"
        class="px-2 py-1 rounded hover:opacity-75 transition-opacity disabled:opacity-30 disabled:cursor-not-allowed"
        :style="{ color: 'var(--text-primary)' }"
        title="Disminuir tamaño de letra"
      >
        <span class="text-sm font-bold">A-</span>
      </button>

      <button
        @click="resetFontSize"
        class="px-2 py-1 rounded hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--text-primary)' }"
        title="Tamaño normal"
      >
        <span class="text-base font-bold">A</span>
      </button>

      <button
        @click="increaseFontSize"
        :disabled="!canIncreaseFontSize"
        class="px-2 py-1 rounded hover:opacity-75 transition-opacity disabled:opacity-30 disabled:cursor-not-allowed"
        :style="{ color: 'var(--text-primary)' }"
        title="Aumentar tamaño de letra"
      >
        <span class="text-lg font-bold">A+</span>
      </button>
    </div>

    <!-- Contrast Toggle -->
    <button
      @click="toggleContrastMode"
      class="px-3 py-2 rounded-lg hover:opacity-75 transition-opacity flex items-center gap-2"
      :style="{ backgroundColor: 'var(--bg-secondary)', color: 'var(--text-primary)' }"
      :title="isHighContrast ? 'Desactivar alto contraste' : 'Activar alto contraste'"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path 
          stroke-linecap="round" 
          stroke-linejoin="round" 
          stroke-width="2" 
          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" 
        />
      </svg>
      <span class="text-sm font-medium">{{ isHighContrast ? 'Alto' : 'Normal' }}</span>
    </button>
  </div>
</template>

<script setup>
import { useAccessibility } from '@/composables/useAccessibility';

const {
  increaseFontSize,
  decreaseFontSize,
  resetFontSize,
  toggleContrastMode,
  canIncreaseFontSize,
  canDecreaseFontSize,
  isHighContrast,
} = useAccessibility();
</script>

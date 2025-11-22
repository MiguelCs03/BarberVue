<template>
  <div class="relative inline-block text-left">
    <button
      @click="isOpen = !isOpen"
      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium shadow-sm hover:opacity-90 transition-opacity"
      :style="{ backgroundColor: 'var(--color-primary)', color: 'white' }"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
      </svg>
      <span>Tema</span>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-56 rounded-lg shadow-lg z-50"
        :style="{ backgroundColor: 'var(--bg-secondary)' }"
      >
        <div class="py-2">
          <!-- Theme Options -->
          <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
            Seleccionar Tema
          </div>
          
          <button
            @click="selectTheme(THEMES.KIDS)"
            class="w-full text-left px-4 py-2 hover:opacity-75 transition-opacity flex items-center gap-3"
            :class="{ 'font-bold': isThemeActive(THEMES.KIDS) }"
            :style="{ color: 'var(--text-primary)' }"
          >
            <span class="text-2xl">🎨</span>
            <span>Niños</span>
            <svg v-if="isThemeActive(THEMES.KIDS)" class="w-5 h-5 ml-auto" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>

          <button
            @click="selectTheme(THEMES.YOUTH)"
            class="w-full text-left px-4 py-2 hover:opacity-75 transition-opacity flex items-center gap-3"
            :class="{ 'font-bold': isThemeActive(THEMES.YOUTH) }"
            :style="{ color: 'var(--text-primary)' }"
          >
            <span class="text-2xl">⚡</span>
            <span>Jóvenes</span>
            <svg v-if="isThemeActive(THEMES.YOUTH)" class="w-5 h-5 ml-auto" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>

          <button
            @click="selectTheme(THEMES.ADULTS)"
            class="w-full text-left px-4 py-2 hover:opacity-75 transition-opacity flex items-center gap-3"
            :class="{ 'font-bold': isThemeActive(THEMES.ADULTS) }"
            :style="{ color: 'var(--text-primary)' }"
          >
            <span class="text-2xl">💼</span>
            <span>Adultos</span>
            <svg v-if="isThemeActive(THEMES.ADULTS)" class="w-5 h-5 ml-auto" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>

          <div class="border-t my-2" :style="{ borderColor: 'var(--text-secondary)' }"></div>

          <!-- Day/Night Toggle -->
          <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
            Modo
          </div>

          <button
            @click="toggleTimeMode"
            class="w-full text-left px-4 py-2 hover:opacity-75 transition-opacity flex items-center gap-3"
            :style="{ color: 'var(--text-primary)' }"
          >
            <span class="text-2xl">{{ isDayMode ? '☀️' : '🌙' }}</span>
            <span>{{ isDayMode ? 'Día' : 'Noche' }}</span>
          </button>

          <button
            v-if="!isAutoTimeMode"
            @click="enableAutoTimeMode"
            class="w-full text-left px-4 py-2 hover:opacity-75 transition-opacity flex items-center gap-3 text-sm"
            :style="{ color: 'var(--text-secondary)' }"
          >
            <span class="text-xl">🔄</span>
            <span>Modo automático</span>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useTheme } from '@/composables/useTheme';

const { 
  THEMES, 
  currentTheme, 
  isDayMode, 
  isAutoTimeMode,
  setTheme, 
  toggleTimeMode, 
  enableAutoTimeMode,
  isThemeActive 
} = useTheme();

const isOpen = ref(false);

const selectTheme = (theme) => {
  setTheme(theme);
  isOpen.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (isOpen.value && !event.target.closest('.relative')) {
    isOpen.value = false;
  }
};

if (typeof window !== 'undefined') {
  window.addEventListener('click', handleClickOutside);
}
</script>

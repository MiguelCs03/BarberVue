<template>
  <div class="p-4 bg-gray-100">
    <h2 class="text-xl font-bold mb-4">Debug de Temas</h2>
    
    <div class="mb-4">
      <p>Tema actual: {{ currentTheme }}</p>
      <p>Modo actual: {{ currentTimeMode }}</p>
      <p>Clases HTML: {{ htmlClasses }}</p>
    </div>

    <div class="flex gap-2">
      <button @click="testTheme('kids')" class="px-4 py-2 bg-pink-500 text-white rounded">
        Niños
      </button>
      <button @click="testTheme('youth')" class="px-4 py-2 bg-purple-500 text-white rounded">
        Jóvenes
      </button>
      <button @click="testTheme('adults')" class="px-4 py-2 bg-teal-700 text-white rounded">
        Adultos
      </button>
    </div>

    <div class="mt-4 p-4 rounded" :style="{ backgroundColor: 'var(--color-primary)', color: 'white' }">
      Esta caja usa var(--color-primary)
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const currentTheme = ref('unknown');
const currentTimeMode = ref('unknown');
const htmlClasses = ref('');

const updateInfo = () => {
  const html = document.documentElement;
  htmlClasses.value = html.className;
  
  // Get theme from classes
  if (html.classList.contains('theme-kids')) currentTheme.value = 'kids';
  else if (html.classList.contains('theme-youth')) currentTheme.value = 'youth';
  else if (html.classList.contains('theme-adults')) currentTheme.value = 'adults';
  else currentTheme.value = 'none';
  
  // Get mode from classes
  if (html.classList.contains('mode-day')) currentTimeMode.value = 'day';
  else if (html.classList.contains('mode-night')) currentTimeMode.value = 'night';
  else currentTimeMode.value = 'none';
};

const testTheme = (theme) => {
  console.log('=== THEME TEST ===');
  console.log('Setting theme to:', theme);
  
  const html = document.documentElement;
  
  // Remove old classes
  html.classList.remove('theme-kids', 'theme-youth', 'theme-adults');
  html.classList.remove('mode-day', 'mode-night');
  
  // Add new classes
  html.classList.add(`theme-${theme}`);
  html.classList.add('mode-day');
  
  console.log('HTML classes after:', html.className);
  console.log('Primary color:', getComputedStyle(html).getPropertyValue('--color-primary'));
  
  updateInfo();
  
  // Force a repaint
  html.style.display = 'none';
  html.offsetHeight; // Trigger reflow
  html.style.display = '';
};

onMounted(() => {
  console.log('ThemeDebug mounted');
  updateInfo();
  
  // Set initial theme
  testTheme('adults');
});
</script>

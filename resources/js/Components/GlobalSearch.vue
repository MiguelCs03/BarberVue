<template>
  <div class="relative" ref="searchContainer">
    <!-- Search Input -->
    <div class="relative">
      <input
        v-model="searchQuery"
        @input="handleSearch"
        @focus="showResults = true"
        @keydown.escape="handleEscape"
        @keydown.down.prevent="navigateResults('down')"
        @keydown.up.prevent="navigateResults('up')"
        @keydown.enter.prevent="selectResult"
        type="text"
        placeholder="Buscar en el sistema..."
        class="w-full pl-10 pr-10 py-2 rounded-lg border-2 transition-all"
        :style="{ 
          borderColor: showResults && searchResults.length > 0 ? 'var(--color-primary)' : 'var(--text-secondary)',
          backgroundColor: 'var(--bg-primary)',
          color: 'var(--text-primary)'
        }"
      />
      
      <!-- Search Icon -->
      <svg 
        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5"
        :style="{ color: isLoading ? 'var(--color-primary)' : 'var(--text-secondary)' }"
        :class="{ 'animate-spin': isLoading }"
        fill="none" 
        stroke="currentColor" 
        viewBox="0 0 24 24"
      >
        <path v-if="!isLoading" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>

      <!-- Clear Button -->
      <button
        v-if="searchQuery"
        @click="handleClear"
        class="absolute right-3 top-1/2 transform -translate-y-1/2 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--text-secondary)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Search Results Dropdown -->
    <div
      v-if="showResults && searchQuery.length >= 2"
      class="absolute top-full left-0 right-0 mt-2 rounded-lg shadow-2xl border-2 max-h-96 overflow-y-auto z-50"
      :style="{ 
        backgroundColor: 'var(--bg-primary)',
        borderColor: 'var(--color-primary)'
      }"
    >
      <!-- Results -->
      <div v-if="searchResults.length > 0" class="py-2">
        <div
          v-for="(result, index) in searchResults"
          :key="result.id"
          @click="navigateToResult(result)"
          @mouseenter="selectedIndex = index"
          class="px-4 py-3 cursor-pointer transition-all"
          :class="{ 'font-semibold': selectedIndex === index }"
          :style="selectedIndex === index ? { backgroundColor: 'var(--bg-secondary)' } : {}"
        >
          <div class="flex items-start gap-3">
            <!-- Icon based on category -->
            <div class="mt-1">
              <svg v-if="result.category === 'Usuarios'" class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <svg v-else-if="result.category === 'Productos'" class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              <svg v-else-if="result.category === 'Servicios'" class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <svg v-else class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <p class="font-medium truncate" :style="{ color: 'var(--text-primary)' }">
                  {{ result.title }}
                </p>
                <span 
                  class="text-xs px-2 py-0.5 rounded"
                  :style="{ 
                    backgroundColor: 'var(--bg-secondary)',
                    color: 'var(--text-secondary)'
                  }"
                >
                  {{ result.category }}
                </span>
              </div>
              <p class="text-sm truncate mt-1" :style="{ color: 'var(--text-secondary)' }">
                {{ result.description }}
              </p>
            </div>

            <svg class="w-5 h-5 flex-shrink-0 mt-1" :style="{ color: 'var(--text-secondary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </div>
      </div>

      <!-- No Results -->
      <div v-else-if="!isLoading" class="px-4 py-8 text-center">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" :style="{ color: 'var(--text-secondary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p :style="{ color: 'var(--text-secondary)' }">
          No se encontraron resultados para "{{ searchQuery }}"
        </p>
      </div>

      <!-- Loading -->
      <div v-else class="px-4 py-8 text-center">
        <div class="animate-spin w-8 h-8 border-4 border-t-transparent rounded-full mx-auto" :style="{ borderColor: 'var(--color-primary)' }"></div>
        <p class="mt-3" :style="{ color: 'var(--text-secondary)' }">Buscando...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const searchQuery = ref('');
const searchResults = ref([]);
const showResults = ref(false);
const selectedIndex = ref(0);
const searchContainer = ref(null);
const isLoading = ref(false);
let searchTimeout = null;

const handleSearch = () => {
  // Clear previous timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  // Reset if query is too short
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }

  // Debounce search
  isLoading.value = true;
  searchTimeout = setTimeout(async () => {
    try {
      //const response = await axios.get('/api/search', {
      const response = await axios.get(route('search'), {
        params: { q: searchQuery.value }
      });
      searchResults.value = response.data;
    } catch (error) {
      console.error('Search error:', error);
      searchResults.value = [];
    } finally {
      isLoading.value = false;
    }
  }, 300);
};

const handleClear = () => {
  searchQuery.value = '';
  searchResults.value = [];
  showResults.value = false;
  selectedIndex.value = 0;
};

const handleEscape = () => {
  showResults.value = false;
  selectedIndex.value = 0;
};

const navigateResults = (direction) => {
  if (searchResults.value.length === 0) return;

  if (direction === 'down') {
    selectedIndex.value = (selectedIndex.value + 1) % searchResults.value.length;
  } else {
    selectedIndex.value = selectedIndex.value === 0 
      ? searchResults.value.length - 1 
      : selectedIndex.value - 1;
  }
};

const selectResult = () => {
  if (searchResults.value.length > 0 && selectedIndex.value >= 0) {
    navigateToResult(searchResults.value[selectedIndex.value]);
  }
};

const navigateToResult = (result) => {
  router.visit(result.url);
  handleClear();
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (searchContainer.value && !searchContainer.value.contains(event.target)) {
    showResults.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
});
</script>

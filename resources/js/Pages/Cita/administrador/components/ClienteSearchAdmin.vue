<template>
    <div class="mb-6 p-4 border-b" :style="{ borderColor: 'var(--border-color)' }">
      <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
        Buscar Cliente (Opcional)
      </h3>
      <p class="text-sm mb-4" :style="{ color: 'var(--text-secondary)' }">
        Puedes buscar un cliente específico o continuar sin seleccionar uno para ver disponibilidad general
      </p>
  
      <!-- Cliente Seleccionado -->
      <div 
        v-if="selectedCliente"
        class="p-4 rounded-lg mb-4"
        :style="{ 
          backgroundColor: 'var(--color-success-light)',
          borderLeft: '4px solid var(--color-success)'
        }"
      >
        <div class="flex justify-between items-center">
          <div>
            <p class="font-semibold" :style="{ color: 'var(--text-primary)' }">
              Cliente seleccionado:
            </p>
            <p :style="{ color: 'var(--text-secondary)' }">
              {{ selectedCliente.name }} ({{ selectedCliente.email }})
            </p>
          </div>
          <button
            @click="clearCliente"
            class="px-4 py-2 rounded-lg hover:opacity-80 transition-opacity"
            :style="{ 
              backgroundColor: 'var(--color-danger)',
              color: 'white'
            }"
          >
            Limpiar
          </button>
        </div>
      </div>
  
      <!-- Formulario de Búsqueda (Solo visible si no hay cliente seleccionado) -->
      <div v-if="!selectedCliente">
        <div class="flex gap-4 mb-4">
          <select 
            v-model="searchType" 
            class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2"
            :style="{ 
              borderColor: 'var(--border-color)',
              backgroundColor: 'var(--bg-secondary)',
              color: 'var(--text-primary)'
            }"
          >
            <option value="username">Buscar por Usuario</option>
            <option value="email">Buscar por Email</option>
          </select>
          
          <input
            v-model="searchTerm"
            type="text"
            class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2"
            :style="{ 
              borderColor: 'var(--border-color)',
              backgroundColor: 'var(--bg-secondary)',
              color: 'var(--text-primary)'
            }"
            placeholder="Escribe el usuario o email..."
            @keyup.enter="searchClientes"
          />
          
          <button
            @click="searchClientes"
            :disabled="searchTerm.length < 2 || loadingSearch"
            class="px-6 py-2 rounded-lg hover:opacity-80 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
            :style="{ 
              backgroundColor: 'var(--color-primary)',
              color: 'white'
            }"
          >
            {{ loadingSearch ? 'Buscando...' : 'Buscar' }}
          </button>
        </div>
  
        <!-- Resultados de Búsqueda -->
        <div 
          v-if="searchResults.length > 0"
          class="border rounded-lg overflow-hidden"
          :style="{ borderColor: 'var(--border-color)' }"
        >
          <div class="max-h-80 overflow-y-auto">
            <table class="w-full">
              <thead 
                class="sticky top-0"
                :style="{ backgroundColor: 'var(--bg-secondary)' }"
              >
                <tr :style="{ borderBottom: '2px solid var(--border-color)' }">
                  <th class="px-4 py-3 text-left font-semibold" :style="{ color: 'var(--text-primary)' }">
                    Nombre
                  </th>
                  <th class="px-4 py-3 text-left font-semibold" :style="{ color: 'var(--text-primary)' }">
                    Email
                  </th>
                  <th class="px-4 py-3 text-center font-semibold" :style="{ color: 'var(--text-primary)' }">
                    Acción
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="cliente in searchResults"
                  :key="cliente.id"
                  class="border-b last:border-b-0 hover:bg-opacity-50 transition-colors"
                  :style="{ 
                    borderColor: 'var(--border-color)'
                  }"
                >
                  <td class="px-4 py-3" :style="{ color: 'var(--text-primary)' }">
                    {{ cliente.name }}
                  </td>
                  <td class="px-4 py-3" :style="{ color: 'var(--text-secondary)' }">
                    {{ cliente.email }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <button
                      @click="selectCliente(cliente)"
                      class="px-4 py-2 rounded-lg hover:opacity-80 transition-opacity"
                      :style="{ 
                        backgroundColor: 'var(--color-primary)',
                        color: 'white'
                      }"
                    >
                      Seleccionar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
  
          <!-- Paginación -->
          <div 
            v-if="pagination.last_page > 1"
            class="flex justify-between items-center px-4 py-3 border-t"
            :style="{ 
              borderColor: 'var(--border-color)',
              backgroundColor: 'var(--bg-secondary)'
            }"
          >
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-4 py-2 rounded-lg hover:opacity-80 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
              :style="{ 
                backgroundColor: 'var(--color-primary)',
                color: 'white'
              }"
            >
              Anterior
            </button>
  
            <span :style="{ color: 'var(--text-secondary)' }">
              Página {{ pagination.current_page }} de {{ pagination.last_page }}
            </span>
  
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-4 py-2 rounded-lg hover:opacity-80 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
              :style="{ 
                backgroundColor: 'var(--color-primary)',
                color: 'white'
              }"
            >
              Siguiente
            </button>
          </div>
        </div>
  
        <!-- Mensaje cuando no hay resultados -->
        <p 
          v-if="hasSearched && searchResults.length === 0 && !loadingSearch"
          class="text-sm mt-2 text-center py-4"
          :style="{ color: 'var(--text-secondary)' }"
        >
          No se encontraron clientes con ese criterio de búsqueda
        </p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, watch } from 'vue';
  import axios from 'axios';
  
  const props = defineProps({
    modelValue: {
      type: Object,
      default: null
    }
  });
  
  const emit = defineEmits(['update:modelValue', 'cleared']);
  
  // Estado
  const searchType = ref('username');
  const searchTerm = ref('');
  const searchResults = ref([]);
  const loadingSearch = ref(false);
  const hasSearched = ref(false);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  });
  
  // Cliente seleccionado
  const selectedCliente = ref(props.modelValue);
  
  // Watch para sincronizar con v-model
  watch(() => props.modelValue, (newVal) => {
    selectedCliente.value = newVal;
  });
  
  watch(selectedCliente, (newVal) => {
    emit('update:modelValue', newVal);
  });
  
  // Métodos
  const searchClientes = async (page = 1) => {
    const term = searchTerm.value.trim();
    
    if (term.length < 2) {
      alert('Por favor, ingresa al menos 2 caracteres para buscar');
      return;
    }
  
    loadingSearch.value = true;
    hasSearched.value = true;
    
    try {
      const endpoint = searchType.value === 'username' 
        ? '/usuarios/busqueda-cliente-username'
        : '/usuarios/busqueda-cliente-email';
      
      const paramName = searchType.value === 'username' ? 'username' : 'email';
      
      const response = await axios.get(endpoint, {
        params: {
          [paramName]: term,
          page: page
        }
      });
      
      searchResults.value = response.data.data || [];
      pagination.value = {
        current_page: response.data.current_page || 1,
        last_page: response.data.last_page || 1,
        per_page: response.data.per_page || 10,
        total: response.data.total || 0
      };
      
    } catch (error) {
      console.error('Error al buscar clientes:', error);
      searchResults.value = [];
      alert('Error al buscar clientes. Por favor intenta nuevamente.');
    } finally {
      loadingSearch.value = false;
    }
  };
  
  const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
      searchClientes(page);
    }
  };
  
  const selectCliente = (cliente) => {
    selectedCliente.value = cliente;
    searchResults.value = [];
    searchTerm.value = '';
    hasSearched.value = false;
  };
  
  const clearCliente = () => {
    selectedCliente.value = null;
    searchResults.value = [];
    searchTerm.value = '';
    hasSearched.value = false;
    emit('cleared');
  };
  
  // Limpiar resultados cuando cambia el tipo de búsqueda
  watch(searchType, () => {
    searchResults.value = [];
    hasSearched.value = false;
  });
  </script>
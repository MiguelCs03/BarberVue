<template>
  <Head title="Movimientos de Inventario" />

  <AppLayout>
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Movimientos de Inventario
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Historial detallado de entradas, salidas y ajustes de productos
      </p>
    </div>

    <Card class="mb-6">
      <div class="flex flex-col space-y-4">
        <div class="flex flex-wrap items-center gap-4">
          
          <div class="flex-1 min-w-[250px]">
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
              </span>
              <input
                v-model="filters.search"
                @input="handleFilters"
                type="text"
                placeholder="Buscar por nombre de producto..."
                class="input w-full pl-10 py-2" 
              />
            </div>
          </div>

          <select v-model="filters.tipo" @change="handleFilters" class="input py-2 text-sm w-full md:w-40" :style="selectStyle">
            <option value="">Todos los Tipos</option>
            <option value="entrada">Entradas</option>
            <option value="salida">Salidas</option>
            <option value="ajuste">Ajustes</option>
          </select>

          <select v-model="filters.estado" @change="handleFilters" class="input py-2 text-sm w-full md:w-40" :style="selectStyle">
            <option value="">Todos los Estados</option>
            <option value="activo">Activos</option>
            <option value="anulado">Anulados</option>
          </select>

          <Link :href="route('movimientos.create')" class="btn-primary px-4 py-2 flex items-center gap-2 ml-auto">
            <PlusIcon class="w-5 h-5" />
            <span>Nuevo</span>
          </Link>
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-2 border-t border-dashed dark:border-gray-700">
          <span class="text-xs font-bold uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
            Filtrar por Fecha:
          </span>

          <div class="relative flex items-center group">
            
            <input
              v-model="filters.desde"
              type="datetime-local"
              @change="handleFilters"
              class="input pl-9 pr-8 py-1.5 text-xs"
              :style="selectStyle"
            />
            <button 
              v-if="filters.desde" 
              @click="clearDate('desde')"
              class="absolute right-2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors"
            >
              <XMarkIcon class="w-3 h-3 text-red-500" />
            </button>
          </div>

          <span class="text-gray-400">a</span>

          <div class="relative flex items-center group">
            <input
              v-model="filters.hasta"
              type="datetime-local"
              @change="handleFilters"
              class="input pl-9 pr-8 py-1.5 text-xs"
              :style="selectStyle"
            />
            <button 
              v-if="filters.hasta" 
              @click="clearDate('hasta')"
              class="absolute right-2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors"
            >
              <XMarkIcon class="w-3 h-3 text-red-500" />
            </button>
          </div>

          <button 
            v-if="hasFilters"
            @click="clearAll"
            class="text-xs font-bold text-red-500 hover:underline flex items-center gap-1"
          >
            <TrashIcon class="w-3 h-3" /> Limpiar Filtros
          </button>
        </div>
      </div>
    </Card>

    <Card>
      <DataTable
        :columns="columns"
        :data="movimientos.data"
        :actions="actions"
      >
        <template #cell-tipo_movimiento="{ value }">
          <Badge :variant="getTipoVariant(value)" class="capitalize">
            <span class="flex items-center gap-1">
              <component :is="getTipoIcon(value)" class="w-4 h-4" />
              {{ value }}
            </span>
          </Badge>
        </template>

        <template #cell-cantidad="{ item }">
          <span class="font-bold" :class="getTextColor(item.tipo_movimiento)">
            {{ item.cantidad }}
          </span>
        </template>
        <template #cell-estado="{ value }">
          <Badge :variant="value === 'activo' ? 'success' : 'danger'" class="uppercase text-[10px] font-bold">
            {{ value === 'activo' ? '✓ Activo' : '✕ Anulado' }}
          </Badge>
        </template>
      </DataTable>
      
      <div v-if="movimientos.links && movimientos.links.length > 3" class="mt-4 flex justify-between items-center">
        <div :style="{ color: 'var(--text-secondary)' }">
          Mostrando {{ movimientos.from }} - {{ movimientos.to }} de {{ movimientos.total }}
        </div>

        <div class="flex gap-2">
          <template v-for="link in movimientos.links" :key="link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'px-3 py-1 rounded transition-all',
                link.active ? 'btn-primary' : 'btn-secondary'
              ]"
              v-html="link.label"
            />

            <span
              v-else
              :class="['px-3 py-1 rounded btn-secondary opacity-50 cursor-not-allowed']"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
      </Card>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import DataTable from '@/Components/DataTable.vue';
import { 
  EyeIcon, ArrowUpCircleIcon, ArrowDownCircleIcon, ExclamationCircleIcon,
  MagnifyingGlassIcon, PlusIcon, CalendarIcon, XMarkIcon, TrashIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  movimientos: Object,
  filters: Object,
});

const filters = reactive({
  search: props.filters.search || '',
  tipo: props.filters.tipo || '',
  estado: props.filters.estado || '',
  desde: props.filters.desde || '',
  hasta: props.filters.hasta || '',
});
const formatLinkLabel = (label) => {
  if (label.includes('Previous')) return '&laquo;';
  if (label.includes('Next')) return '&raquo;';
  return label;
};
const selectStyle = computed(() => ({
  backgroundColor: 'var(--bg-secondary)',
  color: 'var(--text-primary)',
  borderColor: 'var(--border-color)'
}));

const hasFilters = computed(() => {
  return filters.search || filters.tipo || filters.estado || filters.desde || filters.hasta;
});

let timeout = null;
const handleFilters = () => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    router.get(route('movimientos.index'), filters, {
      preserveState: true,
      preserveScroll: true,
      replace: true
    });
  }, 300);
};

// Limpia una fecha específica
const clearDate = (field) => {
  filters[field] = '';
  handleFilters();
};

// Limpia todos los filtros
const clearAll = () => {
  filters.search = '';
  filters.tipo = '';
  filters.estado = '';
  filters.desde = '';
  filters.hasta = '';
  handleFilters();
};

const getTipoVariant = (tipo) => {
  const variants = { entrada: 'success', salida: 'danger', ajuste: 'warning' };
  return variants[tipo] || 'default';
};

const getTipoIcon = (tipo) => {
  const icons = { entrada: ArrowUpCircleIcon, salida: ArrowDownCircleIcon, ajuste: ExclamationCircleIcon };
  return icons[tipo];
};

const getTextColor = (tipo) => {
  return tipo === 'entrada' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
};

const columns = [
  { key: 'producto_nombre', label: 'Producto' },
  { key: 'tipo_movimiento', label: 'Tipo Movimiento' },
  { key: 'cantidad', label: 'Cantidad' },
  { key: 'estado', label: 'Estado' },
  { key: 'fecha', label: 'Fecha Movimiento' },
];

const actions = [
  {
    label: 'Detalles',
    icon: EyeIcon,
    handler: (mov) => router.visit(route('movimientos.show', mov.id)),
    variant: 'primary',
  }
];
</script>
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
      <div class="flex items-center justify-between gap-4">
        <div class="flex-1 max-w-xs">
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <input
              v-model="filters.search"
              @input="handleFilters"
              type="text"
              placeholder="Buscar producto..."
              class="input w-full pl-10 py-2" 
            />
          </div>
        </div>

        <div class="flex items-center gap-2">
          
          <DateRangePicker 
            v-model:desde="filters.desde"
            v-model:hasta="filters.hasta"
            @change="handleFilters"
          />

          <select v-model="filters.tipo" @change="handleFilters" class="input py-2 text-sm" :style="{ backgroundColor: 'var(--bg-secondary)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
            <option value="">Todos los Tipos</option>
            <option value="entrada" :style="{ backgroundColor: 'var(--bg-secondary)' }">Entradas</option>
            <option value="salida" :style="{ backgroundColor: 'var(--bg-secondary)' }">Salidas</option>
            <option value="ajuste" :style="{ backgroundColor: 'var(--bg-secondary)' }">Ajustes</option>
          </select>

          <select v-model="filters.estado" @change="handleFilters" class="input py-2 text-sm" :style="{ backgroundColor: 'var(--bg-secondary)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
            <option value="">Estado</option>
            <option value="activo" :style="{ backgroundColor: 'var(--bg-secondary)' }">Activos</option>
            <option value="anulado" :style="{ backgroundColor: 'var(--bg-secondary)' }">Anulados</option>
          </select>

          <Link
            :href="route('movimientos.create')"
            class="btn-primary whitespace-nowrap px-4 py-2 flex items-center gap-2 shadow-sm"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nuevo</span>
          </Link>
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
          <span 
            v-if="item"
            class="font-bold text-lg"
            :class="item.tipo_movimiento === 'entrada' ? 'text-green-600' : 'text-red-600'"
          >
            {{ item.cantidad }}
          </span>
        </template>

        <template #cell-estado="{ value }">
          <Badge :variant="value === 'activo' ? 'success' : 'danger'">
            {{ value === 'activo' ? 'Válido' : 'Anulado' }}
          </Badge>
        </template>

        <template #cell-fecha="{ value }">
          <span class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
            {{ value }}
          </span>
        </template>
      </DataTable>

      <div v-if="movimientos.links && movimientos.links.length > 3" class="mt-4 flex justify-between items-center border-t pt-4 dark:border-gray-700">
        <div :style="{ color: 'var(--text-secondary)' }" class="text-sm">
          Mostrando {{ movimientos.from }} - {{ movimientos.to }} de {{ movimientos.total }}
        </div>
        <div class="flex gap-1">
          <template v-for="link in movimientos.links" :key="link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'px-3 py-1 rounded text-sm transition-colors',
                link.active ? 'btn-primary' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700'
              ]"
              v-html="link.label"
            />
            <span v-else class="px-3 py-1 rounded text-sm opacity-50 cursor-not-allowed bg-gray-50 dark:bg-gray-900" v-html="link.label" />
          </template>
        </div>
      </div>
    </Card>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import DataTable from '@/Components/DataTable.vue';
import { 
  EyeIcon, 
  ArrowUpCircleIcon, 
  ArrowDownCircleIcon, 
  ExclamationCircleIcon 
} from '@heroicons/vue/24/outline';
import DateRangePicker from './components/DateRangePicker.vue';

const props = defineProps({
  movimientos: Object,
  filters: Object,
});

// Filtros reactivos incluyendo fechas
const filters = reactive({
  search: props.filters.search || '',
  tipo: props.filters.tipo || '',
  estado: props.filters.estado || '',
  desde: props.filters.desde || '',
  hasta: props.filters.hasta || '',
});

// Lógica de filtrado con debounce para evitar múltiples peticiones
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

const columns = [
  { key: 'producto_nombre', label: 'Producto' },
  { key: 'tipo_movimiento', label: 'Tipo De Movimiento' },
  { key: 'cantidad', label: 'Cantidad' },
  { key: 'estado', label: 'Estado' },
  { key: 'fecha', label: 'Fecha' },
];

const actions = [
  {
    label: 'Detalles',
    icon: EyeIcon,
    handler: (mov) => router.visit(route('movimientos.show', mov.id)),
    variant: 'primary',
  }
];

// Helpers para estilos de Badges
const getTipoVariant = (tipo) => {
  const variants = {
    entrada: 'success',
    salida: 'danger',
    ajuste: 'warning'
  };
  return variants[tipo] || 'default';
};

const getTipoIcon = (tipo) => {
  const icons = {
    entrada: ArrowUpCircleIcon,
    salida: ArrowDownCircleIcon,
    ajuste: ExclamationCircleIcon
  };
  return icons[tipo];
};
</script>
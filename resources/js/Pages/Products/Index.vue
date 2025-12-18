<template>
  <Head title="Productos e Inventario" />

  <AppLayout>
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">
          Inventario de Productos
        </h1>
        <p :style="{ color: 'var(--text-secondary)' }" class="text-sm">
          Control de existencias y precios.
        </p>
      </div>
      <Link
        :href="route('products.create')"
        class="btn-primary whitespace-nowrap px-6 py-2.5 flex items-center gap-2 shadow-md self-start md:self-auto"
      >
        <PlusIcon class="w-5 h-5" />
        <span>Nuevo Producto</span>
      </Link>
    </div>

    <Card class="mb-6">
      <div class="flex flex-col lg:flex-row items-center gap-4">
        <div class="relative flex-1 w-full">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </span>
          <input
            v-model="filters.search"
            @input="handleFilters"
            type="text"
            placeholder="Buscar por nombre..."
            class="input w-full pl-10 py-2 text-sm"
          />
        </div>

        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full lg:w-auto">
          <select v-model="filters.estado" @change="handleFilters" class="input py-2 text-sm min-w-[130px]" :style="selectStyle">
            <option value="">Todos los Estados</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
          </select>

          <select v-model="filters.stock_status" @change="handleFilters" class="input py-2 text-sm min-w-[150px]" :style="selectStyle">
            <option value="">Cualquier Stock</option>
            <option value="bajo">⚠️ Stock Bajo</option>
            <option value="normal">Stock Normal</option>
          </select>

          <button 
            @click="clearAll" 
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            title="Limpiar filtros"
          >
            <ArrowPathIcon class="w-5 h-5 text-gray-500" />
          </button>
        </div>
      </div>
    </Card>

    <Card>
      <DataTable
        :columns="columns"
        :data="productos.data"
        :actions="actions"
      >
        <template #cell-nombre="{ value }">
          <span class="font-bold text-sm" :style="{ color: 'var(--text-primary)' }">{{ value }}</span>
        </template>

        <template #cell-stock_actual="{ item, value }">
          <div class="flex items-center gap-2">
            <span 
              class="text-lg font-black"
              :class="item.es_stock_bajo ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300'"
            >
              {{ value }}
            </span>
            <ExclamationTriangleIcon v-if="item.es_stock_bajo" class="w-4 h-4 text-red-500 animate-pulse" />
          </div>
        </template>

        <template #cell-stock_minimo="{ value }">
          <span class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
            {{ value }} uds.
          </span>
        </template>

        <template #cell-precio_venta="{ value }">
          <span class="font-bold text-emerald-600 dark:text-emerald-400">
            Bs. {{ Number(value).toFixed(2) }}
          </span>
        </template>

        <template #cell-estado="{ value }">
          <Badge 
            :variant="getEstadoVariant(value)" 
            class="uppercase text-[10px] font-bold"
            :style="value !== 'activo' && value !== 'inactivo' ? { backgroundColor: '#10b981', color: 'white' } : {}"
          >
            {{ value === 'activo' ? '✓ ' + value : (value === 'inactivo' ? '✕ ' + value : value) }}
          </Badge>
        </template>
      </DataTable>

      <div v-if="productos.links && productos.links.length > 3" class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t pt-4 dark:border-gray-700">
        <div :style="{ color: 'var(--text-secondary)' }" class="text-sm">
          Mostrando {{ productos.from }} - {{ productos.to }} de {{ productos.total }}
        </div>
        <div class="flex gap-2">
          <template v-for="(link, index) in productos.links" :key="index">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="['px-3 py-1 rounded text-sm transition-all', link.active ? 'btn-primary' : 'btn-secondary']"
              v-html="link.label"
            />
            <span v-else class="px-3 py-1 rounded btn-secondary opacity-50 cursor-not-allowed text-sm" v-html="link.label" />
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
  EyeIcon, PencilIcon, PlusIcon, MagnifyingGlassIcon, 
  ExclamationTriangleIcon, ArrowPathIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  productos: Object,
  filters: Object,
});

const filters = reactive({
  search: props.filters.search || '',
  estado: props.filters.estado || '',
  stock_status: props.filters.stock_status || '',
});

const selectStyle = computed(() => ({
  backgroundColor: 'var(--bg-secondary)',
  color: 'var(--text-primary)',
  borderColor: 'var(--border-color)'
}));

let timeout = null;
const handleFilters = () => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    router.get(route('products.index'), filters, {
      preserveState: true, preserveScroll: true, replace: true
    });
  }, 300);
};

const clearAll = () => {
  filters.search = '';
  filters.estado = '';
  filters.stock_status = '';
  handleFilters();
};

const getEstadoVariant = (estado) => {
  if (estado === 'activo') return 'success';
  if (estado === 'inactivo') return 'danger';
  return 'none';
};

const columns = [
  { key: 'nombre', label: 'Producto' },
  { key: 'stock_actual', label: 'Stock Actual' },
  { key: 'stock_minimo', label: 'Stock Mínimo' },
  { key: 'precio_venta', label: 'Precio Venta' },
  { key: 'estado', label: 'Estado' },
];

const actions = [
  {
    label: 'Ver',
    icon: EyeIcon,
    handler: (prod) => router.visit(route('products.show', prod.id)),
    variant: 'primary',
  },
  {
    label: 'Editar',
    icon: PencilIcon,
    handler: (prod) => router.visit(route('products.edit', prod.id)),
    variant: 'primary',
  },
];
</script>
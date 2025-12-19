<template>
  <Head title="Ventas" />

  <AppLayout>
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Ventas
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Gestión de ventas de productos y servicios
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
                placeholder="Buscar por nombre de cliente..."
                class="input w-full pl-10 py-2" 
              />
            </div>
          </div>

          <Link 
            :href="route('ventas.create')" 
            class="btn-primary px-4 py-2 flex items-center gap-2 ml-auto"
          >
            <PlusIcon class="w-5 h-5" />
            <span>Nueva Venta</span>
          </Link>
        </div>
      </div>
    </Card>

    <Card>
      <DataTable
        :columns="columns"
        :data="ventas.data"
        :actions="actions"
      >
        <template #cell-monto_total="{ item }">
          <div class="flex flex-col">
            <span class="font-bold text-green-600 dark:text-green-400">
              Bs. {{ item.total_pagado }} / {{ item.monto_total }}
            </span>
            <div class="w-full bg-gray-200 dark:bg-gray-700 h-1 rounded-full mt-1 overflow-hidden">
              <div 
                class="bg-green-500 h-full" 
                :style="{ width: (item.total_pagado / item.monto_total * 100) + '%' }"
              ></div>
            </div>
          </div>
        </template>
        
        <template #cell-estado_pago="{ value }">
          <Badge :variant="getBadgeVariant(value)" class="capitalize">
            {{ value }}
          </Badge>
        </template>
      </DataTable>
      
      <!-- Pagination -->
      <div v-if="ventas.links && ventas.links.length > 3" class="mt-4 flex justify-between items-center">
        <div :style="{ color: 'var(--text-secondary)' }">
          Mostrando {{ ventas.from }} - {{ ventas.to }} de {{ ventas.total }}
        </div>

        <div class="flex gap-2">
          <template v-for="link in ventas.links" :key="link.label">
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
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import DataTable from '@/Components/DataTable.vue';
import { 
  MagnifyingGlassIcon, 
  PlusIcon,
  EyeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  ventas: Object,
  filters: Object,
});

const filters = reactive({
  search: props.filters.search || '',
});

let timeout = null;
const handleFilters = () => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    router.get(route('ventas.index'), filters, {
      preserveState: true,
      preserveScroll: true,
      replace: true
    });
  }, 300);
};

const getBadgeVariant = (estado) => {
  if (estado === 'completado') return 'success';
  if (estado === 'parcial') return 'warning';
  return 'danger';
};

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'cliente', label: 'Cliente' },
  { key: 'barbero', label: 'Atendido por' },
  { key: 'monto_total', label: 'Pago / Total' },
  { key: 'estado_pago', label: 'Estado' },
  { key: 'fecha', label: 'Fecha' },
];

const actions = [
  {
    label: 'Detalles',
    icon: EyeIcon,
    handler: (venta) => router.visit(route('ventas.show', venta.id)),
    variant: 'primary',
  }
];
</script>

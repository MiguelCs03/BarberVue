<template>
  <Head title="Gestión de Productos" />

  <AppLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Gestión de Productos
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Administra el inventario de productos de la barbería
      </p>
    </div>

    <!-- Filters and Actions -->
    <Card class="mb-6">
      <div class="flex flex-col md:flex-row gap-4 justify-between">
        <!-- Search -->
        <div class="flex-1 max-w-md">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar por nombre o descripción..."
            class="input w-full"
          />
        </div>

        <!-- Stock Filter and Create Button -->
        <div class="flex gap-3 items-center">
          <select
            v-model="stockFilter"
            class="input"
          >
            <option value="">Todos los productos</option>
            <option value="low">Stock bajo</option>
            <option value="normal">Stock normal</option>
            <option value="out">Sin stock</option>
          </select>

          <Link
            :href="route('products.create')"
            class="btn-primary whitespace-nowrap"
          >
            + Nuevo Producto
          </Link>
        </div>
      </div>
    </Card>

    <!-- Products Table -->
    <Card>
      <DataTable
        :columns="columns"
        :data="filteredProducts"
        :actions="actions"
      >
        <template #cell-precio_venta="{ value }">
          <span class="font-semibold" :style="{ color: 'var(--color-primary)' }">
            Bs. {{ value.toFixed(2) }}
          </span>
        </template>

        <template #cell-stock_actual="{ item }">
          <div class="flex items-center gap-2">
            <Badge :variant="getStockBadgeVariant(item)">
              {{ item.stock_actual }} unidades
            </Badge>
            <span v-if="item.stock_actual <= item.stock_minimo" class="text-red-600 text-xs">
              ⚠️ Bajo
            </span>
          </div>
        </template>
      </DataTable>
    </Card>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import DataTable from '@/Components/DataTable.vue';

// Static mock data matching database schema
const products = ref([
  {
    id: 1,
    nombre: 'Shampoo profesional',
    descripcion: 'Shampoo para todo tipo de cabello 500ml',
    precio_venta: 8.50,
    stock_minimo: 2,
    stock_actual: 10,
  },
  {
    id: 2,
    nombre: 'Pomada',
    descripcion: 'Pomada para peinar 100ml',
    precio_venta: 6.00,
    stock_minimo: 2,
    stock_actual: 15,
  },
  {
    id: 3,
    nombre: 'Acondicionador',
    descripcion: 'Acondicionador 500ml',
    precio_venta: 7.50,
    stock_minimo: 2,
    stock_actual: 12,
  },
  {
    id: 4,
    nombre: 'Gel fijador',
    descripcion: 'Gel para peinar 150ml',
    precio_venta: 5.50,
    stock_minimo: 2,
    stock_actual: 20,
  },
  {
    id: 5,
    nombre: 'Cera para cabello',
    descripcion: 'Cera moldeadora 80ml',
    precio_venta: 12.00,
    stock_minimo: 3,
    stock_actual: 1,
  },
  {
    id: 6,
    nombre: 'Aceite para barba',
    descripcion: 'Aceite nutritivo para barba 50ml',
    precio_venta: 15.00,
    stock_minimo: 2,
    stock_actual: 0,
  },
]);

// Filters
const searchQuery = ref('');
const stockFilter = ref('');

// Table columns
const columns = [
  { key: 'nombre', label: 'Producto' },
  { key: 'descripcion', label: 'Descripción' },
  { key: 'precio_venta', label: 'Precio' },
  { key: 'stock_actual', label: 'Stock' },
];

// Table actions
const actions = [
  {
    label: 'Ver',
    handler: (product) => router.visit(route('products.show', product.id)),
    variant: 'primary',
  },
  {
    label: 'Editar',
    handler: (product) => router.visit(route('products.edit', product.id)),
    variant: 'primary',
  },
  {
    label: 'Eliminar',
    handler: (product) => {
      if (confirm(`¿Estás seguro de eliminar "${product.nombre}"?`)) {
        alert('Funcionalidad de eliminación pendiente (backend)');
      }
    },
    variant: 'danger',
  },
];

// Filtered products
const filteredProducts = computed(() => {
  let filtered = products.value;

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (product) =>
        product.nombre.toLowerCase().includes(query) ||
        product.descripcion.toLowerCase().includes(query)
    );
  }

  // Filter by stock status
  if (stockFilter.value === 'low') {
    filtered = filtered.filter((p) => p.stock_actual > 0 && p.stock_actual <= p.stock_minimo);
  } else if (stockFilter.value === 'normal') {
    filtered = filtered.filter((p) => p.stock_actual > p.stock_minimo);
  } else if (stockFilter.value === 'out') {
    filtered = filtered.filter((p) => p.stock_actual === 0);
  }

  return filtered;
});

// Helper functions
const getStockBadgeVariant = (product) => {
  if (product.stock_actual === 0) return 'danger';
  if (product.stock_actual <= product.stock_minimo) return 'warning';
  return 'success';
};
</script>

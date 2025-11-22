<template>
  <Head title="Gestión de Inventario" />

  <AppLayout>
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Gestión de Inventario
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Registra y consulta movimientos de inventario
      </p>
    </div>

    <!-- Filters and Actions -->
    <Card class="mb-6">
      <div class="flex flex-col md:flex-row gap-4 justify-between">
        <div class="flex gap-3">
          <select v-model="typeFilter" class="input">
            <option value="">Todos los movimientos</option>
            <option value="ingreso">Ingresos</option>
            <option value="salida_venta">Salidas</option>
            <option value="ajuste">Ajustes</option>
          </select>

          <select v-model="productFilter" class="input">
            <option value="">Todos los productos</option>
            <option v-for="product in products" :key="product.id" :value="product.id">
              {{ product.nombre }}
            </option>
          </select>
        </div>

        <Link :href="route('inventory.create')" class="btn-primary whitespace-nowrap">
          + Nuevo Movimiento
        </Link>
      </div>
    </Card>

    <!-- Movements Table -->
    <Card>
      <DataTable
        :columns="columns"
        :data="filteredMovements"
        :actions="actions"
      >
        <template #cell-tipo_movimiento="{ value }">
          <Badge :variant="getTypeBadgeVariant(value)">
            {{ getTypeLabel(value) }}
          </Badge>
        </template>

        <template #cell-cantidad="{ item }">
          <span 
            class="font-semibold"
            :style="{ color: item.tipo_movimiento === 'salida_venta' ? '#DC2626' : 'var(--color-primary)' }"
          >
            {{ item.tipo_movimiento === 'salida_venta' ? '-' : '+' }}{{ item.cantidad }}
          </span>
        </template>

        <template #cell-fecha="{ value }">
          {{ formatDate(value) }}
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

// Mock products
const products = ref([
  { id: 1, nombre: 'Shampoo profesional' },
  { id: 2, nombre: 'Pomada' },
  { id: 3, nombre: 'Acondicionador' },
  { id: 4, nombre: 'Gel fijador' },
]);

// Mock movements
const movements = ref([
  { id: 1, producto_id: 1, producto_nombre: 'Shampoo profesional', usuario_nombre: 'Miguel Acs', cantidad: 10, motivo: 'Stock inicial', tipo_movimiento: 'ingreso', fecha: '2025-11-22 10:00:00' },
  { id: 2, producto_id: 2, producto_nombre: 'Pomada', usuario_nombre: 'Miguel Acs', cantidad: 15, motivo: 'Stock inicial', tipo_movimiento: 'ingreso', fecha: '2025-11-22 10:05:00' },
  { id: 3, producto_id: 1, producto_nombre: 'Shampoo profesional', usuario_nombre: 'Miguel Acs', cantidad: 5, motivo: 'Compra mensual', tipo_movimiento: 'ingreso', fecha: '2025-11-22 14:00:00' },
  { id: 4, producto_id: 2, producto_nombre: 'Pomada', usuario_nombre: 'Miguel Acs', cantidad: 2, motivo: 'Ajuste por rotura', tipo_movimiento: 'ajuste', fecha: '2025-11-22 15:00:00' },
  { id: 5, producto_id: 3, producto_nombre: 'Acondicionador', usuario_nombre: 'Miguel Acs', cantidad: 12, motivo: 'Stock inicial acondicionador', tipo_movimiento: 'ingreso', fecha: '2025-11-22 16:00:00' },
  { id: 6, producto_id: 4, producto_nombre: 'Gel fijador', usuario_nombre: 'Miguel Acs', cantidad: 20, motivo: 'Compra proveedor A', tipo_movimiento: 'ingreso', fecha: '2025-11-22 17:00:00' },
  { id: 7, producto_id: 2, producto_nombre: 'Pomada', usuario_nombre: 'Ana Gomez', cantidad: 5, motivo: 'Venta mostrador', tipo_movimiento: 'salida_venta', fecha: '2025-11-22 18:00:00' },
]);

const typeFilter = ref('');
const productFilter = ref('');

const columns = [
  { key: 'producto_nombre', label: 'Producto' },
  { key: 'tipo_movimiento', label: 'Tipo' },
  { key: 'cantidad', label: 'Cantidad' },
  { key: 'motivo', label: 'Motivo' },
  { key: 'usuario_nombre', label: 'Usuario' },
  { key: 'fecha', label: 'Fecha' },
];

const actions = [
  {
    label: 'Ver',
    handler: (movement) => router.visit(route('inventory.show', movement.id)),
    variant: 'primary',
  },
];

const filteredMovements = computed(() => {
  let filtered = movements.value;

  if (typeFilter.value) {
    filtered = filtered.filter(m => m.tipo_movimiento === typeFilter.value);
  }

  if (productFilter.value) {
    filtered = filtered.filter(m => m.producto_id === parseInt(productFilter.value));
  }

  return filtered;
});

const getTypeBadgeVariant = (type) => {
  if (type === 'ingreso') return 'success';
  if (type === 'salida_venta') return 'danger';
  return 'warning';
};

const getTypeLabel = (type) => {
  const labels = {
    'ingreso': 'Ingreso',
    'salida_venta': 'Salida',
    'ajuste': 'Ajuste',
  };
  return labels[type] || type;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

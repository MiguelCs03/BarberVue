<template>
  <Head title="Detalle de Movimiento" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('inventory.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver
      </Link>

      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Detalle del Movimiento
      </h1>
    </div>

    <Card>
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Producto
            </label>
            <p class="mt-1 text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
              {{ movement.producto_nombre }}
            </p>
          </div>

          <div>
            <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Tipo de Movimiento
            </label>
            <div class="mt-1">
              <Badge :variant="getTypeBadgeVariant(movement.tipo_movimiento)">
                {{ getTypeLabel(movement.tipo_movimiento) }}
              </Badge>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Cantidad
            </label>
            <p 
              class="mt-1 text-2xl font-bold"
              :style="{ color: movement.tipo_movimiento === 'salida_venta' ? '#DC2626' : 'var(--color-primary)' }"
            >
              {{ movement.tipo_movimiento === 'salida_venta' ? '-' : '+' }}{{ movement.cantidad }}
            </p>
          </div>

          <div>
            <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Fecha
            </label>
            <p class="mt-1 text-lg" :style="{ color: 'var(--text-primary)' }">
              {{ formatDate(movement.fecha) }}
            </p>
          </div>
        </div>

        <div>
          <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
            Motivo
          </label>
          <p class="mt-1" :style="{ color: 'var(--text-primary)' }">
            {{ movement.motivo }}
          </p>
        </div>

        <div>
          <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
            Registrado por
          </label>
          <p class="mt-1" :style="{ color: 'var(--text-primary)' }">
            {{ movement.usuario_nombre }}
          </p>
        </div>
      </div>
    </Card>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';

const movementId = window.location.pathname.split('/').filter(Boolean).pop();

const mockMovements = {
  '1': { id: 1, producto_nombre: 'Shampoo profesional', usuario_nombre: 'Miguel Acs', cantidad: 10, motivo: 'Stock inicial', tipo_movimiento: 'ingreso', fecha: '2025-11-22 10:00:00' },
  '2': { id: 2, producto_nombre: 'Pomada', usuario_nombre: 'Miguel Acs', cantidad: 15, motivo: 'Stock inicial', tipo_movimiento: 'ingreso', fecha: '2025-11-22 10:05:00' },
  '3': { id: 3, producto_nombre: 'Shampoo profesional', usuario_nombre: 'Miguel Acs', cantidad: 5, motivo: 'Compra mensual', tipo_movimiento: 'ingreso', fecha: '2025-11-22 14:00:00' },
  '7': { id: 7, producto_nombre: 'Pomada', usuario_nombre: 'Ana Gomez', cantidad: 5, motivo: 'Venta mostrador', tipo_movimiento: 'salida_venta', fecha: '2025-11-22 18:00:00' },
};

const movement = mockMovements[movementId] || mockMovements['1'];

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

<template>
  <Head :title="`Producto: ${product.nombre}`" />

  <AppLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <Link
        :href="route('products.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver a la lista
      </Link>

      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
            {{ product.nombre }}
          </h1>
          <p :style="{ color: 'var(--text-secondary)' }">
            Detalles del producto
          </p>
        </div>

        <Link
          :href="route('products.edit', product.id)"
          class="btn-primary"
        >
          Editar Producto
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Info -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Product Details -->
        <Card>
          <h2 class="text-xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Información del Producto
          </h2>

          <div class="space-y-4">
            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Nombre
              </label>
              <p class="mt-1 text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                {{ product.nombre }}
              </p>
            </div>

            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Descripción
              </label>
              <p class="mt-1" :style="{ color: 'var(--text-primary)' }">
                {{ product.descripcion }}
              </p>
            </div>

            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Precio de Venta
              </label>
              <p class="mt-1 text-2xl font-bold" :style="{ color: 'var(--color-primary)' }">
                Bs. {{ product.precio_venta.toFixed(2) }}
              </p>
            </div>
          </div>
        </Card>

        <!-- Stock History (Placeholder) -->
        <Card>
          <h2 class="text-xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Historial de Movimientos
          </h2>
          <div class="text-center py-8" :style="{ color: 'var(--text-secondary)' }">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p>El historial de movimientos estará disponible cuando se integre el backend</p>
          </div>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Stock Status -->
        <Card>
          <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Estado del Stock
          </h3>

          <div class="space-y-4">
            <!-- Stock Actual -->
            <div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                  Stock Actual
                </span>
                <Badge :variant="getStockBadgeVariant()">
                  {{ getStockStatus() }}
                </Badge>
              </div>
              <p class="text-3xl font-bold" :style="{ color: getStockColor() }">
                {{ product.stock_actual }}
              </p>
              <p class="text-xs mt-1" :style="{ color: 'var(--text-secondary)' }">
                unidades disponibles
              </p>
            </div>

            <!-- Stock Mínimo -->
            <div class="pt-4 border-t" :style="{ borderColor: 'var(--text-secondary)' }">
              <span class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Stock Mínimo
              </span>
              <p class="text-xl font-semibold mt-1" :style="{ color: 'var(--text-primary)' }">
                {{ product.stock_minimo }} unidades
              </p>
            </div>

            <!-- Progress Bar -->
            <div class="pt-4">
              <div class="flex justify-between text-xs mb-2" :style="{ color: 'var(--text-secondary)' }">
                <span>Nivel de stock</span>
                <span>{{ stockPercentage }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all"
                  :style="{ 
                    width: `${Math.min(stockPercentage, 100)}%`,
                    backgroundColor: getStockColor()
                  }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Low Stock Alert -->
          <div 
            v-if="product.stock_actual <= product.stock_minimo"
            class="mt-4 p-3 rounded-lg"
            :class="product.stock_actual === 0 ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200'"
          >
            <div class="flex items-start gap-2">
              <svg 
                class="w-5 h-5 mt-0.5"
                :class="product.stock_actual === 0 ? 'text-red-600' : 'text-yellow-600'"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <div>
                <p 
                  class="text-sm font-medium"
                  :class="product.stock_actual === 0 ? 'text-red-800' : 'text-yellow-800'"
                >
                  {{ product.stock_actual === 0 ? 'Sin stock' : 'Stock bajo' }}
                </p>
                <p 
                  class="text-xs mt-1"
                  :class="product.stock_actual === 0 ? 'text-red-700' : 'text-yellow-700'"
                >
                  {{ product.stock_actual === 0 ? 'Producto agotado. Reabastecer urgente.' : 'Considera reabastecer pronto.' }}
                </p>
              </div>
            </div>
          </div>
        </Card>

        <!-- Quick Actions -->
        <Card>
          <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Acciones Rápidas
          </h3>
          <div class="space-y-2">
            <button
              class="w-full btn-primary text-left"
              @click="alert('Funcionalidad pendiente: Agregar stock')"
            >
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Agregar Stock
            </button>
            <button
              class="w-full btn-secondary text-left"
              @click="alert('Funcionalidad pendiente: Ajustar stock')"
            >
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Ajustar Stock
            </button>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';

// Get product ID from route
const productId = window.location.pathname.split('/').filter(Boolean).pop();

// Mock product data
const mockProducts = {
  '1': { id: 1, nombre: 'Shampoo profesional', descripcion: 'Shampoo para todo tipo de cabello 500ml', precio_venta: 8.50, stock_minimo: 2, stock_actual: 10 },
  '2': { id: 2, nombre: 'Pomada', descripcion: 'Pomada para peinar 100ml', precio_venta: 6.00, stock_minimo: 2, stock_actual: 15 },
  '3': { id: 3, nombre: 'Acondicionador', descripcion: 'Acondicionador 500ml', precio_venta: 7.50, stock_minimo: 2, stock_actual: 12 },
  '4': { id: 4, nombre: 'Gel fijador', descripcion: 'Gel para peinar 150ml', precio_venta: 5.50, stock_minimo: 2, stock_actual: 20 },
  '5': { id: 5, nombre: 'Cera para cabello', descripcion: 'Cera moldeadora 80ml', precio_venta: 12.00, stock_minimo: 3, stock_actual: 1 },
  '6': { id: 6, nombre: 'Aceite para barba', descripcion: 'Aceite nutritivo para barba 50ml', precio_venta: 15.00, stock_minimo: 2, stock_actual: 0 },
};

const product = mockProducts[productId] || mockProducts['1'];

const stockPercentage = computed(() => {
  if (product.stock_minimo === 0) return 100;
  return Math.round((product.stock_actual / (product.stock_minimo * 3)) * 100);
});

const getStockBadgeVariant = () => {
  if (product.stock_actual === 0) return 'danger';
  if (product.stock_actual <= product.stock_minimo) return 'warning';
  return 'success';
};

const getStockStatus = () => {
  if (product.stock_actual === 0) return 'Sin stock';
  if (product.stock_actual <= product.stock_minimo) return 'Stock bajo';
  return 'Stock normal';
};

const getStockColor = () => {
  if (product.stock_actual === 0) return '#DC2626';
  if (product.stock_actual <= product.stock_minimo) return '#D97706';
  return 'var(--color-primary)';
};
</script>

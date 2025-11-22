<template>
  <Head title="Editar Producto" />

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

      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Editar Producto
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Actualiza la información del producto
      </p>
    </div>

    <!-- Form -->
    <Card>
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Nombre -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Nombre del Producto *
          </label>
          <input
            v-model="form.nombre"
            type="text"
            required
            class="input"
            placeholder="Ej: Shampoo profesional"
          />
          <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</p>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Descripción *
          </label>
          <textarea
            v-model="form.descripcion"
            required
            rows="3"
            class="input"
            placeholder="Descripción detallada del producto"
          ></textarea>
          <p v-if="errors.descripcion" class="mt-1 text-sm text-red-600">{{ errors.descripcion }}</p>
        </div>

        <!-- Precio de Venta -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Precio de Venta (Bs.) *
          </label>
          <input
            v-model.number="form.precio_venta"
            type="number"
            step="0.01"
            min="0"
            required
            class="input"
            placeholder="0.00"
          />
          <p v-if="errors.precio_venta" class="mt-1 text-sm text-red-600">{{ errors.precio_venta }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Stock Mínimo -->
          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Stock Mínimo *
            </label>
            <input
              v-model.number="form.stock_minimo"
              type="number"
              min="0"
              required
              class="input"
              placeholder="0"
            />
            <p class="mt-1 text-xs" :style="{ color: 'var(--text-secondary)' }">
              Nivel mínimo antes de alerta
            </p>
            <p v-if="errors.stock_minimo" class="mt-1 text-sm text-red-600">{{ errors.stock_minimo }}</p>
          </div>

          <!-- Stock Actual -->
          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Stock Actual *
            </label>
            <input
              v-model.number="form.stock_actual"
              type="number"
              min="0"
              required
              class="input"
              placeholder="0"
            />
            <p class="mt-1 text-xs" :style="{ color: 'var(--text-secondary)' }">
              Cantidad disponible en inventario
            </p>
            <p v-if="errors.stock_actual" class="mt-1 text-sm text-red-600">{{ errors.stock_actual }}</p>
          </div>
        </div>

        <!-- Stock Warning -->
        <div 
          v-if="form.stock_actual <= form.stock_minimo && form.stock_actual > 0"
          class="p-4 rounded-lg bg-yellow-50 border border-yellow-200"
        >
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <p class="text-sm font-medium text-yellow-800">
                Advertencia: Stock bajo
              </p>
              <p class="text-sm text-yellow-700 mt-1">
                El stock actual está en o por debajo del nivel mínimo. Considera reabastecer pronto.
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-4">
          <button
            type="submit"
            class="btn-primary"
          >
            Guardar Cambios
          </button>
          <Link
            :href="route('products.index')"
            class="btn-secondary"
          >
            Cancelar
          </Link>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

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

const currentProduct = mockProducts[productId] || mockProducts['1'];

const form = ref({
  nombre: currentProduct.nombre,
  descripcion: currentProduct.descripcion,
  precio_venta: currentProduct.precio_venta,
  stock_minimo: currentProduct.stock_minimo,
  stock_actual: currentProduct.stock_actual,
});

const errors = ref({});

const handleSubmit = () => {
  // Client-side validation
  errors.value = {};

  if (!form.value.nombre) {
    errors.value.nombre = 'El nombre es requerido';
  }

  if (!form.value.descripcion) {
    errors.value.descripcion = 'La descripción es requerida';
  }

  if (form.value.precio_venta <= 0) {
    errors.value.precio_venta = 'El precio debe ser mayor a 0';
  }

  if (form.value.stock_minimo < 0) {
    errors.value.stock_minimo = 'El stock mínimo no puede ser negativo';
  }

  if (form.value.stock_actual < 0) {
    errors.value.stock_actual = 'El stock actual no puede ser negativo';
  }

  if (Object.keys(errors.value).length === 0) {
    alert('Producto actualizado exitosamente (funcionalidad de backend pendiente)');
    router.visit(route('products.index'));
  }
};
</script>

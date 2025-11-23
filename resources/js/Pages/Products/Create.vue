<template>
  <Head title="Crear Producto" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('products.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver
      </Link>

      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Crear Nuevo Producto
      </h1>
    </div>

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
            placeholder="Ej: Shampoo Profesional"
          />
          <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Descripción
          </label>
          <textarea
            v-model="form.descripcion"
            rows="3"
            class="input"
            placeholder="Descripción del producto"
          ></textarea>
          <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
            <p v-if="form.errors.precio_venta" class="mt-1 text-sm text-red-600">{{ form.errors.precio_venta }}</p>
          </div>

          <!-- Stock Actual -->
          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Stock Inicial *
            </label>
            <input
              v-model.number="form.stock_actual"
              type="number"
              min="0"
              required
              class="input"
              placeholder="0"
            />
            <p v-if="form.errors.stock_actual" class="mt-1 text-sm text-red-600">{{ form.errors.stock_actual }}</p>
          </div>

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
            <p v-if="form.errors.stock_minimo" class="mt-1 text-sm text-red-600">{{ form.errors.stock_minimo }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-4">
          <button
            type="submit"
            class="btn-primary"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Creando...' : 'Crear Producto' }}
          </button>
          <Link :href="route('products.index')" class="btn-secondary">
            Cancelar
          </Link>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const form = useForm({
  nombre: '',
  descripcion: '',
  precio_venta: 0,
  stock_actual: 0,
  stock_minimo: 0,
});

const handleSubmit = () => {
  form.post(route('products.store'), {
    preserveScroll: true,
  });
};
</script>

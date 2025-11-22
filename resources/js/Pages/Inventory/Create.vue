<template>
  <Head title="Registrar Movimiento" />

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
        Registrar Movimiento de Inventario
      </h1>
    </div>

    <Card>
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Producto *
          </label>
          <select v-model="form.producto_id" required class="input">
            <option value="">Seleccionar producto</option>
            <option v-for="product in products" :key="product.id" :value="product.id">
              {{ product.nombre }} (Stock: {{ product.stock_actual }})
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Tipo de Movimiento *
          </label>
          <select v-model="form.tipo_movimiento" required class="input">
            <option value="">Seleccionar tipo</option>
            <option value="ingreso">Ingreso</option>
            <option value="salida_venta">Salida</option>
            <option value="ajuste">Ajuste</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Cantidad *
          </label>
          <input
            v-model.number="form.cantidad"
            type="number"
            min="1"
            required
            class="input"
            placeholder="0"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Motivo *
          </label>
          <textarea
            v-model="form.motivo"
            required
            rows="3"
            class="input"
            placeholder="Describe el motivo del movimiento"
          ></textarea>
        </div>

        <div class="flex gap-3 pt-4">
          <button type="submit" class="btn-primary">
            Registrar Movimiento
          </button>
          <Link :href="route('inventory.index')" class="btn-secondary">
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

const products = ref([
  { id: 1, nombre: 'Shampoo profesional', stock_actual: 10 },
  { id: 2, nombre: 'Pomada', stock_actual: 15 },
  { id: 3, nombre: 'Acondicionador', stock_actual: 12 },
  { id: 4, nombre: 'Gel fijador', stock_actual: 20 },
]);

const form = ref({
  producto_id: '',
  tipo_movimiento: '',
  cantidad: 1,
  motivo: '',
});

const handleSubmit = () => {
  alert('Movimiento registrado exitosamente (funcionalidad de backend pendiente)');
  router.visit(route('inventory.index'));
};
</script>

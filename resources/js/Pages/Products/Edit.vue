<template>
  <Head :title="`Editar: ${product.nombre}`" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('products.show', product.id)"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <ArrowLeftIcon class="w-5 h-5" />
        Cancelar y volver
      </Link>
      <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">Modificar Producto</h1>
    </div>

    <div class="max-w-7xl mx-auto">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          
          <div class="md:col-span-2 space-y-6">
            <Card title="Información General">
              <div class="space-y-4">
                <div>
                  <label class="label">Nombre del Producto</label>
                  <input v-model="form.nombre" type="text" class="input w-full" :style="inputStyle(form.errors.nombre)" />
                  <p v-if="form.errors.nombre" class="error-msg">{{ form.errors.nombre }}</p>
                </div>

                <div>
                  <label class="label">Descripción</label>
                  <textarea v-model="form.descripcion" rows="4" class="input w-full resize-none" :style="inputStyle(form.errors.descripcion)"></textarea>
                </div>
              </div>
            </Card>

            <Card title="Precios y Alertas">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="label">Precio de Venta (Bs.)</label>
                  <input v-model="form.precio_venta" type="number" step="0.01" class="input w-full" :style="inputStyle(form.errors.precio_venta)" />
                </div>
                <div>
                  <label class="label">Stock Mínimo</label>
                  <input v-model="form.stock_minimo" type="number" class="input w-full" :style="inputStyle(form.errors.stock_minimo)" />
                </div>
              </div>
            </Card>
          </div>

          <div class="space-y-6">
            <Card title="Estado y Stock">
              <div class="space-y-4">
                <div>
                  <label class="label">Estado Operativo</label>
                  <select v-model="form.estado" class="input w-full" :style="inputStyle(form.errors.estado)">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                  </select>
                </div>

                <div class="p-4 rounded-xl bg-gray-100 dark:bg-gray-800/50 border border-dashed border-gray-300 dark:border-gray-600">
                  <label class="label opacity-60">Stock Actual (No editable)</label>
                  <p class="text-2xl font-black opacity-50">{{ product.stock_actual }} uds.</p>
                  <Link :href="route('movimientos.create')" class="text-xs text-blue-500 hover:underline mt-2 block">
                    ¿Ajustar stock? Ir a movimientos
                  </Link>
                </div>
              </div>
            </Card>

            <button
              type="submit"
              :disabled="form.processing"
              class="btn-primary w-full py-3 flex items-center justify-center gap-2"
              :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
            >
              <CloudArrowUpIcon v-if="!form.processing" class="w-5 h-5" />
              <ArrowPathIcon v-else class="w-5 h-5 animate-spin" />
              {{ form.processing ? 'Guardando...' : 'Actualizar Producto' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import { ArrowLeftIcon, CloudArrowUpIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ product: Object });

const form = useForm({
  nombre: props.product.nombre,
  descripcion: props.product.descripcion,
  precio_venta: props.product.precio_venta,
  stock_minimo: props.product.stock_minimo,
  estado: props.product.estado,
});

const submit = () => form.put(route('products.update', props.product.id));

const inputStyle = (error) => ({
  backgroundColor: 'var(--bg-primary)',
  color: 'var(--text-primary)',
  borderColor: error ? '#EF4444' : 'var(--border-color)'
});
</script>

<style scoped>
.label { @apply block text-sm font-bold mb-2; color: var(--text-primary); }
.error-msg { @apply mt-1 text-sm text-red-500 font-medium; }
</style>
<template>
  <Head :title="`Producto: ${product.nombre}`" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('products.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <ArrowLeftIcon class="w-5 h-5" />
        Volver a la lista
      </Link>

      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">
              {{ product.nombre }}
            </h1>
            <Badge :variant="product.estado === 'activo' ? 'success' : 'danger'">
              {{ product.estado === 'activo' ? 'Activo' : 'Inactivo' }}
            </Badge>
          </div>
          <p :style="{ color: 'var(--text-secondary)' }">
            Última Actualización {{ DateHelper.formatRelativeDate(product.updated_at) }}
          </p>
        </div>
         
         <Link v-if="product.es_stock_bajo" :href="route('movimientos.create', product.id)" class="btn-primary whitespace-nowrap flex items-center gap-2">
          <PlusIcon class="w-5 h-5" />
          Agregar Stock
        </Link>
        <Link :href="route('products.edit', product.id)" class="btn-primary whitespace-nowrap flex items-center gap-2">
          <PencilSquareIcon class="w-5 h-5" />
          Editar Producto
        </Link>
      </div>
    </div>

    <div class="space-y-6">
      <Card>
        <div class="flex items-center gap-2 mb-6">
          <CubeIcon class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" />
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
            Detalles de Inventario
          </h2>
        </div>

        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                Stock Actual
              </label>
              <div class="flex items-center gap-3">
                <span 
                  class="text-3xl font-black" 
                  :class="product.es_stock_bajo ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'"
                >
                  {{ product.stock_actual }}
                </span>
                <Badge v-if="product.es_stock_bajo" variant="danger" class="animate-pulse">
                  Bajo Stock
                </Badge>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                Stock Mínimo Permitido
              </label>
              <p class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
                {{ product.stock_minimo }} <span class="text-sm font-normal">uds.</span>
              </p>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                Precio de Venta
              </label>
              <div class="flex items-baseline gap-1">
                <span class="text-sm font-bold" :style="{ color: 'var(--text-secondary)' }">Bs.</span>
                <span class="text-3xl font-bold" :style="{ color: 'var(--color-primary)' }">
                  {{ Number(product.precio_venta).toFixed(2) }}
                </span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                Estado del Producto
              </label>
              <div class="flex items-center gap-2">
                <Badge :variant="product.estado === 'activo' ? 'success' : 'danger'" class="text-sm">
                  {{ product.estado === 'activo' ? '✓ Operativo' : '✕ Deshabilitado' }}
                </Badge>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-dashed dark:border-gray-700">
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
              Descripción del Producto
            </label>
            <p class="text-base leading-relaxed" :style="{ color: product.descripcion ? 'var(--text-primary)' : 'var(--text-secondary)' }">
              {{ product.descripcion || 'Sin descripción detallada disponible.' }}
            </p>
          </div>
        </div>
      </Card>

      <Card>
        <div class="flex items-center gap-2 mb-6">
          <ClockIcon class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" />
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
            Trazabilidad del Registro
          </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="flex gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50">
            <div class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900/30">
              <CalendarDaysIcon class="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest mb-1 text-gray-500">Fecha de Alta</label>
              <p class="font-bold text-lg" :style="{ color: 'var(--text-primary)' }">
                {{ DateHelper.formatDate(product.created_at) }}
              </p>
              <p class="text-sm opacity-70" :style="{ color: 'var(--text-secondary)' }">
                Hace {{ DateHelper.formatRelativeDate(product.created_at) }}
              </p>
            </div>
          </div>

          <div class="flex gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50">
            <div class="w-12 h-12 rounded-full flex items-center justify-center bg-amber-100 dark:bg-amber-900/30">
              <ArrowPathIcon class="w-6 h-6 text-amber-600" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest mb-1 text-gray-500">Último Cambio</label>
              <p class="font-bold text-lg" :style="{ color: 'var(--text-primary)' }">
                {{ DateHelper.formatDate(product.updated_at) }}
              </p>
              <p class="text-sm opacity-70" :style="{ color: 'var(--text-secondary)' }">
                Modificado {{ DateHelper.formatRelativeDate(product.updated_at) }}
              </p>
            </div>
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { DateHelper } from '@/helpers/dateHelper';
import { 
  ArrowLeftIcon, 
  PencilSquareIcon, 
  CubeIcon, 
  ClockIcon, 
  CalendarDaysIcon, 
  ArrowPathIcon, 
  PlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});
</script>
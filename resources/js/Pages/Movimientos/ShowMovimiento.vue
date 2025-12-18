<template>
  <Head :title="`Movimiento #${movimiento.id} - ${movimiento.producto_nombre}`" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('movimientos.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <ArrowLeftIcon class="w-5 h-5" />
        Volver al listado
      </Link>

      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">
              Movimiento #{{ movimiento.id }}
            </h1>
            <Badge :variant="movimiento.estado === 'activo' ? 'success' : 'danger'">
              {{ movimiento.estado === 'activo' ? 'Activo' : 'Anulado' }}
            </Badge>
          </div>
          <p :style="{ color: 'var(--text-secondary)' }">
            Ultima Actualización {{ DateHelper.formatRelativeDate(movimiento.updated_at) }}
          </p>
        </div>

        <div class="flex items-center gap-2">
          <Link 
            v-if="movimiento.estado === 'activo'"
            :href="route('movimientos.edit', movimiento.id)" 
            class="btn-primary flex items-center gap-2"
          >
            <PencilSquareIcon class="w-5 h-5" />
            Editar
          </Link>

          <button 
            v-if="movimiento.estado === 'activo'"
            @click="confirmAnulacion"
            class="btn-danger flex items-center gap-2"
          >
            <TrashIcon class="w-5 h-5" />
            Anular
          </button>
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <Card>
        <div class="flex items-center gap-2 mb-6">
          <component 
            :is="getTipoIcon(movimiento.tipo_movimiento)" 
            class="w-6 h-6" 
            :class="getTextColor(movimiento.tipo_movimiento)" 
          />
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
            Información del Registro
          </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
              Producto
            </label>
            <div class="flex flex-col">
              <p class="text-lg font-bold" :style="{ color: 'var(--text-primary)' }">
                {{ movimiento.producto_nombre }}
              </p>
              <Link :href="route('products.show', movimiento.producto_id)" class="text-sm font-medium text-blue-500 hover:underline">
                Ver ficha técnica
              </Link>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
              Tipo de Movimiento
            </label>
            <p class="text-lg font-bold capitalize" :class="getTextColor(movimiento.tipo_movimiento)">
              {{ movimiento.tipo_movimiento }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
              Cantidad
            </label>
            <div class="flex items-baseline gap-1">
              <span class="text-2xl font-black" :class="getTextColor(movimiento.tipo_movimiento)">
                {{ movimiento.cantidad }}
              </span>
              <span class="text-sm font-semibold" :style="{ color: 'var(--text-secondary)' }">unidades.</span>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
              Fecha Operación
            </label>
            <p class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
              {{ DateHelper.formatDate(movimiento.fecha) }}
            </p>
          </div>
        </div>

        <div class="pt-6 mt-6 border-t border-dashed dark:border-gray-700">
          <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
            Motivo / Descripción
          </label>
          <p class="text-base leading-relaxed italic" :style="{ color: 'var(--text-primary)' }">
            {{ movimiento.motivo || 'Sin descripción detallada' }}
          </p>
        </div>
      </Card>

      <Card>
        <div class="flex items-center gap-2 mb-4">
          <ClockIcon class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" />
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
            Cronología del Registro
          </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-800">
              <CalendarIcon class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" />
            </div>
            <div>
              <p class="text-xs font-bold uppercase" :style="{ color: 'var(--text-secondary)' }">Creado el</p>
              <p class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ DateHelper.formatDate(movimiento.created_at) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-800">
              <ArrowPathIcon class="w-6 h-6" :style="{ color: 'var(--color-secondary)' }" />
            </div>
            <div>
              <p class="text-xs font-bold uppercase" :style="{ color: 'var(--text-secondary)' }">Última Modificación</p>
              <p class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ DateHelper.formatDate(movimiento.updated_at) }}</p>
            </div>
          </div>
        </div>
      </Card>

      <div v-if="movimiento.estado === 'anulado'" class="p-4 rounded-xl border flex gap-4 bg-red-50 border-red-200 dark:bg-red-900/10 dark:border-red-800">
        <ExclamationTriangleIcon class="w-8 h-8 text-red-600" />
        <div>
          <h3 class="font-bold text-red-600">Este movimiento ha sido anulado</h3>
          <p class="text-sm text-red-500">
            Las cantidades descritas arriba ya no afectan el stock actual de los productos.
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { DateHelper } from '@/helpers/dateHelper';
import Swal from 'sweetalert2';
import { 
  ArrowLeftIcon, 
  ArrowUpCircleIcon, 
  ArrowDownCircleIcon, 
  ExclamationCircleIcon,
  CalendarIcon,
  TrashIcon,
  PencilSquareIcon,
  ClockIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  movimiento: Object
});

const getTextColor = (tipo) => {
    if (tipo === 'entrada') return 'text-green-600';
    if (tipo === 'salida') return 'text-red-600';
    return 'text-amber-600';
};

const getTipoIcon = (tipo) => {
  const icons = {
    entrada: ArrowUpCircleIcon,
    salida: ArrowDownCircleIcon,
    ajuste: ExclamationCircleIcon
  };
  return icons[tipo];
};

const confirmAnulacion = () => {
    Swal.fire({
        title: '¿Anular movimiento?',
        text: 'Esta acción revertirá el stock del producto afectado. ¿Deseas continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: 'var(--text-secondary)',
        confirmButtonText: 'Sí, anular registro',
        cancelButtonText: 'Cancelar',
        background: 'var(--bg-primary)',
        color: 'var(--text-primary)'
    }).then((result) => {
        if (result.isConfirmed) {
            // Ajusta esta ruta según tu web.php (ejemplo: movimientos.anular)
            router.post(route('movimientos.anular', props.movimiento.id), {}, {
                preserveScroll: true
            });
        }
    });
};
</script>
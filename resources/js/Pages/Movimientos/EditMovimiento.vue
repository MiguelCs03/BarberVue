<template>
  <Head :title="`Editar Movimiento #${movimiento.id}`" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('movimientos.show', movimiento.id)"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <ArrowLeftIcon class="w-5 h-5" />
        Cancelar y volver
      </Link>

      <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">
        Editar Registro
      </h1>
    </div>

    <div class="max-w-7xl mx-auto">
      <form @submit.prevent="submit">
        <Card class="mb-6 border-l-8" :class="getBorderColor(movimiento.tipo_movimiento)">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="p-3 rounded-full text-white" :class="getTipoBg(movimiento.tipo_movimiento)">
                <component :is="getTipoIcon(movimiento.tipo_movimiento)" class="w-6 h-6" />
              </div>
              <div>
                <p class="text-xs font-bold uppercase tracking-widest" :style="{ color: 'var(--text-secondary)' }">
                  {{ movimiento.tipo_movimiento }} de Inventario
                </p>
                <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
                  {{ movimiento.producto_nombre }}
                </h2>
              </div>
            </div>
            <div class="text-right">
              <p class="text-xs font-bold uppercase tracking-widest" :style="{ color: 'var(--text-secondary)' }">Cantidad</p>
              <p class="text-2xl font-black" :class="getTextColor(movimiento.tipo_movimiento, movimiento.cantidad)">
                {{ movimiento.cantidad }}
              </p>
            </div>
          </div>
        </Card>

        <Card>
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
                Fecha y Hora del Movimiento
              </label>
              <div class="relative">
                <CalendarIcon class="absolute left-3 top-3 w-5 h-5 opacity-40" />
                <input
                  v-model="form.fecha"
                  type="datetime-local"
                  class="w-full pl-10 pr-4 py-2 rounded-lg border focus:ring-2 transition-all outline-none"
                  :style="{ 
                    backgroundColor: 'var(--bg-primary)', 
                    color: 'var(--text-primary)',
                    borderColor: form.errors.fecha ? '#EF4444' : 'var(--border-color)'
                  }"
                />
              </div>
              <p v-if="form.errors.fecha" class="mt-1 text-sm text-red-500 font-medium">{{ form.errors.fecha }}</p>
            </div>

            <div>
              <label class="block text-sm font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
                Motivo o Descripción
              </label>
              <textarea
                v-model="form.motivo"
                rows="4"
                placeholder="Escriba el motivo del cambio..."
                class="w-full px-4 py-3 rounded-lg border focus:ring-2 transition-all outline-none resize-none"
                :style="{ 
                  backgroundColor: 'var(--bg-primary)', 
                  color: 'var(--text-primary)',
                  borderColor: form.errors.motivo ? '#EF4444' : 'var(--border-color)'
                }"
              ></textarea>
              <p v-if="form.errors.motivo" class="mt-1 text-sm text-red-500 font-medium">{{ form.errors.motivo }}</p>
              <p class="mt-2 text-xs italic" :style="{ color: 'var(--text-secondary)' }">
                * Este campo es opcional.
              </p>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
              <Link :href="route('movimientos.show', movimiento.id)" class="px-6 py-2 font-bold opacity-70 hover:opacity-100 transition-opacity">
                Descartar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="btn-primary px-10 flex items-center gap-2"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
              >
                <CloudArrowUpIcon v-if="!form.processing" class="w-5 h-5" />
                <ArrowPathIcon v-else class="w-5 h-5 animate-spin" />
                {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
              </button>
            </div>
          </div>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import { 
  ArrowLeftIcon, 
  CalendarIcon, 
  CloudArrowUpIcon,
  ArrowPathIcon,
  ArrowUpCircleIcon,
  ArrowDownCircleIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  movimiento: Object
});

const form = useForm({
  fecha: props.movimiento.fecha || '',
  motivo: props.movimiento.motivo || '',
});

const submit = () => {
  form.put(route('movimientos.update', props.movimiento.id), {
    preserveScroll: true,
  });
};

// --- Helpers Visuales ---

const getSimbolo = (tipo, cantidad) => {
  if (tipo === 'entrada') return '+';
  if (tipo === 'salida') return '-';
  return cantidad >= 0 ? '+' : ''; // En ajuste depende del signo original
};

const getTextColor = (tipo, cantidad) => {
  if (tipo === 'entrada') return 'text-green-600';
  if (tipo === 'salida') return 'text-red-600';
  return cantidad < 0 ? 'text-red-600' : 'text-amber-600';
};

const getBorderColor = (tipo) => {
  const borders = {
    entrada: 'border-green-500',
    salida: 'border-red-500',
    ajuste: 'border-amber-500'
  };
  return borders[tipo] || 'border-gray-500';
};

const getTipoBg = (tipo) => {
  const bgs = {
    entrada: 'bg-green-500',
    salida: 'bg-red-500',
    ajuste: 'bg-amber-500'
  };
  return bgs[tipo] || 'bg-gray-500';
};

const getTipoIcon = (tipo) => {
  const icons = {
    entrada: ArrowUpCircleIcon,
    salida: ArrowDownCircleIcon,
    ajuste: ExclamationCircleIcon
  };
  return icons[tipo];
};
</script>
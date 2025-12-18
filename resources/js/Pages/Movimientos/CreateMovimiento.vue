<template>
  <Head title="Registrar Movimiento de Inventario" />

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

      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Nuevo Movimiento
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Registre una entrada, salida o ajuste manual de stock.
      </p>
    </div>

    <div class="max-w-7xl mx-auto">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          
          <div class="md:col-span-2 space-y-6">
            <Card>
              <div class="space-y-6">
                <div>
                  <label class="block text-sm font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
                    Producto <span class="text-red-500">*</span>
                  </label>
                  <select 
                    v-model="form.producto_id"
                    class="input w-full py-2.5"
                    :style="inputStyle(form.errors.producto_id)"
                  >
                    <option value="" disabled>Seleccione un producto...</option>
                    <option v-for="prod in productos" :key="prod.id" :value="prod.id">
                      {{ prod.nombre }} (Stock actual: {{ prod.stock_actual }})
                    </option>
                  </select>
                  <p v-if="form.errors.producto_id" class="mt-1 text-sm text-red-500 font-medium">{{ form.errors.producto_id }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
                      Cantidad <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="form.cantidad"
                      type="number"
                      placeholder="Ej: 10"
                      class="input w-full py-2"
                      :style="inputStyle(form.errors.cantidad)"
                    />
                    <p v-if="form.errors.cantidad" class="mt-1 text-sm text-red-500 font-medium">{{ form.errors.cantidad }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
                      Fecha y Hora <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <CalendarIcon class="absolute left-3 top-2.5 w-5 h-5 opacity-40" />
                      <input 
                        v-model="form.fecha"
                        type="datetime-local"
                        class="input w-full pl-10 py-2"
                        :style="inputStyle(form.errors.fecha)"
                      />
                    </div>
                    <p v-if="form.errors.fecha" class="mt-1 text-sm text-red-500 font-medium">{{ form.errors.fecha }}</p>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
                    Motivo / Descripción
                  </label>
                  <textarea 
                    v-model="form.motivo"
                    rows="3"
                    placeholder="Escriba la razón del movimiento..."
                    class="input w-full py-2 px-4 resize-none"
                    :style="inputStyle(form.errors.motivo)"
                  ></textarea>
                </div>
              </div>
            </Card>
          </div>

          <div class="space-y-6">
            <Card title="Tipo de Movimiento">
              <div class="flex flex-col gap-3">
                <button 
                  v-for="tipo in tipos" 
                  :key="tipo"
                  type="button"
                  @click="form.tipo_movimiento = tipo"
                  class="flex items-center justify-between px-4 py-3 rounded-xl border-2 transition-all group"
                  :class="form.tipo_movimiento === tipo ? getSelectedClass(tipo) : 'border-transparent bg-gray-50 dark:bg-gray-800/50 opacity-60'"
                >
                  <div class="flex items-center gap-3">
                    <component :is="getTipoIcon(tipo)" class="w-6 h-6" :class="getTextColor(tipo)" />
                    <span class="font-bold capitalize" :style="{ color: 'var(--text-primary)' }">{{ tipo }}</span>
                  </div>
                  <CheckCircleIcon v-if="form.tipo_movimiento === tipo" class="w-5 h-5" />
                </button>
              </div>
              <p v-if="form.errors.tipo_movimiento" class="mt-2 text-xs text-red-500 font-bold uppercase text-center">
                Debe seleccionar un tipo
              </p>
            </Card>

            <button
              type="submit"
              :disabled="form.processing"
              class="btn-primary w-full py-4 flex items-center justify-center gap-3 shadow-lg"
              :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
            >
              <CloudArrowUpIcon v-if="!form.processing" class="w-6 h-6" />
              <ArrowPathIcon v-else class="w-6 h-6 animate-spin" />
              <span class="text-lg font-bold">
                {{ form.processing ? 'Registrando...' : 'Confirmar Registro' }}
              </span>
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
import { 
  ArrowLeftIcon, 
  CalendarIcon, 
  CloudArrowUpIcon,
  ArrowPathIcon,
  ArrowUpCircleIcon,
  ArrowDownCircleIcon,
  ExclamationCircleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  productos: Array,
  tipos: Array
});

// Inicializamos el formulario con la fecha actual formateada para datetime-local
const now = new Date();
const formattedNow = now.toISOString().slice(0, 16);

const form = useForm({
  producto_id: '',
  tipo_movimiento: 'entrada',
  cantidad: null,
  motivo: '',
  fecha: formattedNow
});

const submit = () => {
  form.post(route('movimientos.store'), {
    onSuccess: () => form.reset(),
    preserveScroll: true
  });
};

// --- Estilos Dinámicos ---

const inputStyle = (error) => ({
  backgroundColor: 'var(--bg-primary)',
  color: 'var(--text-primary)',
  borderColor: error ? '#EF4444' : 'var(--border-color)'
});

const getSelectedClass = (tipo) => {
  if (tipo === 'entrada') return 'border-green-500 bg-green-50 dark:bg-green-900/10';
  if (tipo === 'salida') return 'border-red-500 bg-red-50 dark:bg-red-900/10';
  return 'border-amber-500 bg-amber-50 dark:bg-amber-900/10';
};

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
</script>
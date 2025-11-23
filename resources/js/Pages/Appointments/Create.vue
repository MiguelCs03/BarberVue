<template>
  <Head title="Nueva Cita" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('appointments.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver
      </Link>

      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Nueva Cita
      </h1>
    </div>

    <Card>
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Cliente *
            </label>
            <select v-model="form.cliente_id" required class="input">
              <option value="">Seleccionar cliente</option>
              <option v-for="client in clients" :key="client.id" :value="client.id">
                {{ client.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Barbero *
            </label>
            <select v-model="form.barbero_id" required class="input">
              <option value="">Seleccionar barbero</option>
              <option v-for="barber in barbers" :key="barber.id" :value="barber.id">
                {{ barber.nombre }}
              </option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Servicios *
          </label>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <label
              v-for="service in services"
              :key="service.id"
              class="flex items-center gap-2 p-3 rounded-lg border-2 cursor-pointer transition-all"
              :style="{ 
                borderColor: form.servicios.includes(service.id) ? 'var(--color-primary)' : 'var(--text-secondary)',
                backgroundColor: form.servicios.includes(service.id) ? 'var(--bg-secondary)' : 'transparent'
              }"
            >
              <input
                type="checkbox"
                :value="service.id"
                v-model="form.servicios"
                class="w-4 h-4"
              />
              <div class="flex-1">
                <p class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ service.nombre }}</p>
                <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
                  {{ service.duracion_estimada }} min - Bs. {{ service.precio.toFixed(2) }}
                </p>
              </div>
            </label>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Fecha *
            </label>
            <input
              v-model="form.fecha"
              type="date"
              required
              class="input"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Hora *
            </label>
            <input
              v-model="form.hora"
              type="time"
              required
              class="input"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Tipo de Pago *
            </label>
            <select v-model="form.tipo_pago_id" required class="input">
              <option value="">Seleccionar tipo</option>
              <option value="1">Contado</option>
              <option value="2">Tarjeta</option>
              <option value="3">Transferencia</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Pago Inicial (Bs.)
            </label>
            <input
              v-model.number="form.pago_inicial"
              type="number"
              step="0.01"
              min="0"
              class="input"
              placeholder="0.00"
            />
            <p class="text-xs mt-1" :style="{ color: 'var(--text-secondary)' }">
              Dejar en 0 para pago completo al finalizar
            </p>
          </div>
        </div>

        <!-- Total Estimado -->
        <div v-if="totalEstimado > 0" class="p-4 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)' }">
          <div class="flex justify-between items-center">
            <span class="font-medium" :style="{ color: 'var(--text-primary)' }">Total Estimado:</span>
            <span class="text-2xl font-bold" :style="{ color: 'var(--color-primary)' }">
              Bs. {{ totalEstimado.toFixed(2) }}
            </span>
          </div>
          <div v-if="form.pago_inicial > 0" class="mt-2 text-sm" :style="{ color: 'var(--text-secondary)' }">
            Saldo pendiente: Bs. {{ (totalEstimado - form.pago_inicial).toFixed(2) }}
          </div>
        </div>

        <div class="flex gap-3 pt-4">
          <button type="submit" class="btn-primary">
            Crear Cita
          </button>
          <Link :href="route('appointments.index')" class="btn-secondary">
            Cancelar
          </Link>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const clients = ref([
  { id: 5, nombre: 'Luis García' },
  { id: 6, nombre: 'Sofía Reyes' },
  { id: 7, nombre: 'Marcos Vargas' },
  { id: 8, nombre: 'Valeria Ramos' },
]);

const barbers = ref([
  { id: 2, nombre: 'Juan Pérez' },
  { id: 3, nombre: 'Carlos López' },
]);

const services = ref([
  { id: 1, nombre: 'Corte de cabello', duracion_estimada: 30, precio: 15.00 },
  { id: 2, nombre: 'Afeitado', duracion_estimada: 20, precio: 10.00 },
  { id: 3, nombre: 'Corte + Barba', duracion_estimada: 45, precio: 25.00 },
  { id: 4, nombre: 'Coloración', duracion_estimada: 60, precio: 40.00 },
  { id: 5, nombre: 'Tratamiento capilar', duracion_estimada: 40, precio: 20.00 },
  { id: 6, nombre: 'Perfilado de barba', duracion_estimada: 15, precio: 8.00 },
]);

const form = ref({
  cliente_id: '',
  barbero_id: '',
  servicios: [],
  fecha: '',
  hora: '',
  tipo_pago_id: '',
  pago_inicial: 0,
});

const totalEstimado = computed(() => {
  return form.value.servicios.reduce((total, serviceId) => {
    const service = services.value.find(s => s.id === serviceId);
    return total + (service ? service.precio : 0);
  }, 0);
});

const handleSubmit = () => {
  if (form.value.servicios.length === 0) {
    alert('Debes seleccionar al menos un servicio');
    return;
  }
  alert('Cita creada exitosamente (funcionalidad de backend pendiente)');
  router.visit(route('appointments.index'));
};
</script>

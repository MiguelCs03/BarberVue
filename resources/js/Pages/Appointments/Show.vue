<template>
  <Head :title="`Cita #${appointment.id}`" />

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

      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
            Cita #{{ appointment.id }}
          </h1>
          <Badge :variant="getStatusBadgeVariant(appointment.estado)">
            {{ getStatusLabel(appointment.estado) }}
          </Badge>
        </div>

        <Link :href="route('appointments.edit', appointment.id)" class="btn-primary">
          Editar Cita
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Appointment Details -->
        <Card>
          <h2 class="text-xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Detalles de la Cita
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Cliente
              </label>
              <p class="mt-1 text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                {{ appointment.cliente_nombre }}
              </p>
            </div>

            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Barbero
              </label>
              <p class="mt-1 text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                {{ appointment.barbero_nombre }}
              </p>
            </div>

            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Fecha y Hora
              </label>
              <p class="mt-1 text-lg" :style="{ color: 'var(--text-primary)' }">
                {{ formatDateTime(appointment.fecha_hora_inicio) }}
              </p>
            </div>

            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Duración
              </label>
              <p class="mt-1 text-lg" :style="{ color: 'var(--text-primary)' }">
                {{ getDuration(appointment.fecha_hora_inicio, appointment.fecha_hora_fin) }} min
              </p>
            </div>
          </div>

          <div class="mt-6">
            <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Servicios
            </label>
            <p class="mt-1" :style="{ color: 'var(--text-primary)' }">
              {{ appointment.servicios }}
            </p>
          </div>
        </Card>

        <!-- Payment Info -->
        <Card>
          <h2 class="text-xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Información de Pago
          </h2>

          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span :style="{ color: 'var(--text-secondary)' }">Tipo de Pago:</span>
              <Badge variant="info">{{ appointment.tipo_pago }}</Badge>
            </div>

            <div class="flex justify-between items-center">
              <span :style="{ color: 'var(--text-secondary)' }">Pago Inicial:</span>
              <span class="text-lg font-semibold" :style="{ color: 'var(--color-primary)' }">
                Bs. {{ appointment.pago_inicial.toFixed(2) }}
              </span>
            </div>

            <div v-if="appointment.pago_inicial === 0" class="p-3 rounded-lg bg-yellow-50 border border-yellow-200">
              <p class="text-sm text-yellow-800">
                ⚠️ Sin pago inicial. El cliente pagará el total al finalizar el servicio.
              </p>
            </div>
          </div>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <Card>
          <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Acciones
          </h3>
          <div class="space-y-2">
            <button
              v-if="appointment.estado === 'pendiente_pago_adelanto'"
              class="w-full btn-primary text-left"
              @click="alert('Funcionalidad pendiente: Confirmar pago')"
            >
              ✓ Confirmar Pago
            </button>
            <button
              v-if="appointment.estado === 'confirmada'"
              class="w-full btn-primary text-left"
              @click="alert('Funcionalidad pendiente: Completar cita')"
            >
              ✓ Completar Cita
            </button>
            <button
              v-if="appointment.estado !== 'cancelada' && appointment.estado !== 'completada'"
              class="w-full btn-secondary text-left"
              @click="handleCancel"
            >
              ✗ Cancelar Cita
            </button>
          </div>
        </Card>

        <!-- Status Info -->
        <Card>
          <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Estado
          </h3>
          <div class="text-center py-4">
            <Badge :variant="getStatusBadgeVariant(appointment.estado)" class="text-lg px-4 py-2">
              {{ getStatusLabel(appointment.estado) }}
            </Badge>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';

const appointmentId = window.location.pathname.split('/').filter(Boolean).pop();

const mockAppointments = {
  '1': { id: 1, cliente_nombre: 'Luis García', barbero_nombre: 'Juan Pérez', servicios: 'Corte de cabello', tipo_pago: 'contado', pago_inicial: 0.00, fecha_hora_inicio: '2025-11-10 10:00:00', fecha_hora_fin: '2025-11-10 10:30:00', estado: 'pendiente_pago_adelanto' },
  '2': { id: 2, cliente_nombre: 'Sofía Reyes', barbero_nombre: 'Carlos López', servicios: 'Corte + Barba', tipo_pago: 'tarjeta', pago_inicial: 5.00, fecha_hora_inicio: '2025-11-11 14:00:00', fecha_hora_fin: '2025-11-11 14:45:00', estado: 'confirmada' },
  '3': { id: 3, cliente_nombre: 'Luis García', barbero_nombre: 'Carlos López', servicios: 'Afeitado', tipo_pago: 'contado', pago_inicial: 0.00, fecha_hora_inicio: '2025-11-09 16:00:00', fecha_hora_fin: '2025-11-09 16:30:00', estado: 'completada' },
};

const appointment = mockAppointments[appointmentId] || mockAppointments['1'];

const getStatusBadgeVariant = (status) => {
  const variants = {
    'pendiente_pago_adelanto': 'warning',
    'confirmada': 'info',
    'completada': 'success',
    'cancelada': 'danger',
  };
  return variants[status] || 'default';
};

const getStatusLabel = (status) => {
  const labels = {
    'pendiente_pago_adelanto': 'Pendiente Pago',
    'confirmada': 'Confirmada',
    'completada': 'Completada',
    'cancelada': 'Cancelada',
  };
  return labels[status] || status;
};

const formatDateTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getDuration = (start, end) => {
  const startDate = new Date(start);
  const endDate = new Date(end);
  return Math.round((endDate - startDate) / 60000);
};

const handleCancel = () => {
  if (confirm('¿Estás seguro de cancelar esta cita?')) {
    alert('Cita cancelada (funcionalidad de backend pendiente)');
    router.visit(route('appointments.index'));
  }
};
</script>

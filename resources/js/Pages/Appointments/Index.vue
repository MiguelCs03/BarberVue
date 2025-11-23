<template>
  <Head title="Gestión de Citas" />

  <AppLayout>
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Gestión de Citas
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Administra las citas de los clientes
      </p>
    </div>

    <!-- Filters and Actions -->
    <Card class="mb-6">
      <div class="flex flex-col md:flex-row gap-4 justify-between">
        <div class="flex gap-3">
          <select v-model="statusFilter" class="input">
            <option value="">Todos los estados</option>
            <option value="pendiente_pago_adelanto">Pendiente Pago</option>
            <option value="confirmada">Confirmada</option>
            <option value="completada">Completada</option>
            <option value="cancelada">Cancelada</option>
          </select>

          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar cliente..."
            class="input"
          />
        </div>

        <Link :href="route('appointments.create')" class="btn-primary whitespace-nowrap">
          + Nueva Cita
        </Link>
      </div>
    </Card>

    <!-- Appointments Table -->
    <Card>
      <DataTable
        :columns="columns"
        :data="filteredAppointments"
        :actions="actions"
      >
        <template #cell-estado="{ value }">
          <Badge :variant="getStatusBadgeVariant(value)">
            {{ getStatusLabel(value) }}
          </Badge>
        </template>

        <template #cell-fecha_hora_inicio="{ value }">
          {{ formatDateTime(value) }}
        </template>

        <template #cell-pago_inicial="{ item }">
          <span v-if="item.pago_inicial > 0" class="font-semibold" :style="{ color: 'var(--color-primary)' }">
            Bs. {{ item.pago_inicial.toFixed(2) }}
          </span>
          <span v-else :style="{ color: 'var(--text-secondary)' }">
            Sin adelanto
          </span>
        </template>
      </DataTable>
    </Card>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import DataTable from '@/Components/DataTable.vue';

// Mock appointments
const appointments = ref([
  { id: 1, cliente_nombre: 'Luis García', barbero_nombre: 'Juan Pérez', servicios: 'Corte de cabello', tipo_pago: 'contado', pago_inicial: 0.00, fecha_hora_inicio: '2025-11-10 10:00:00', fecha_hora_fin: '2025-11-10 10:30:00', estado: 'pendiente_pago_adelanto' },
  { id: 2, cliente_nombre: 'Sofía Reyes', barbero_nombre: 'Carlos López', servicios: 'Corte + Barba', tipo_pago: 'tarjeta', pago_inicial: 5.00, fecha_hora_inicio: '2025-11-11 14:00:00', fecha_hora_fin: '2025-11-11 14:45:00', estado: 'confirmada' },
  { id: 3, cliente_nombre: 'Luis García', barbero_nombre: 'Carlos López', servicios: 'Afeitado', tipo_pago: 'contado', pago_inicial: 0.00, fecha_hora_inicio: '2025-11-09 16:00:00', fecha_hora_fin: '2025-11-09 16:30:00', estado: 'completada' },
  { id: 4, cliente_nombre: 'Marcos Vargas', barbero_nombre: 'Juan Pérez', servicios: 'Corte de cabello', tipo_pago: 'contado', pago_inicial: 0.00, fecha_hora_inicio: '2025-11-12 09:30:00', fecha_hora_fin: '2025-11-12 10:00:00', estado: 'pendiente_pago_adelanto' },
  { id: 5, cliente_nombre: 'Valeria Ramos', barbero_nombre: 'Carlos López', servicios: 'Tratamiento capilar, Perfilado de barba', tipo_pago: 'contado', pago_inicial: 0.00, fecha_hora_inicio: '2025-11-12 11:00:00', fecha_hora_fin: '2025-11-12 11:30:00', estado: 'confirmada' },
  { id: 6, cliente_nombre: 'Flor Méndez', barbero_nombre: 'Juan Pérez', servicios: 'Corte + Barba, Perfilado de barba', tipo_pago: 'tarjeta', pago_inicial: 3.00, fecha_hora_inicio: '2025-11-13 15:00:00', fecha_hora_fin: '2025-11-13 15:45:00', estado: 'confirmada' },
]);

const statusFilter = ref('');
const searchQuery = ref('');

const columns = [
  { key: 'cliente_nombre', label: 'Cliente' },
  { key: 'barbero_nombre', label: 'Barbero' },
  { key: 'servicios', label: 'Servicios' },
  { key: 'fecha_hora_inicio', label: 'Fecha/Hora' },
  { key: 'pago_inicial', label: 'Pago Inicial' },
  { key: 'estado', label: 'Estado' },
];

const actions = [
  {
    label: 'Ver',
    handler: (appointment) => router.visit(route('appointments.show', appointment.id)),
    variant: 'primary',
  },
  {
    label: 'Editar',
    handler: (appointment) => router.visit(route('appointments.edit', appointment.id)),
    variant: 'primary',
  },
  {
    label: 'Cancelar',
    handler: (appointment) => {
      if (confirm('¿Estás seguro de cancelar esta cita?')) {
        alert('Cita cancelada (funcionalidad de backend pendiente)');
      }
    },
    variant: 'danger',
  },
];

const filteredAppointments = computed(() => {
  let filtered = appointments.value;

  if (statusFilter.value) {
    filtered = filtered.filter(a => a.estado === statusFilter.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(a => 
      a.cliente_nombre.toLowerCase().includes(query) ||
      a.barbero_nombre.toLowerCase().includes(query)
    );
  }

  return filtered;
});

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
</script>

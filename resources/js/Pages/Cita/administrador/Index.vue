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
              <option value="pendiente">Pendiente</option>
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
  
          <Link :href="route('citas-admin.create')" class="btn-primary whitespace-nowrap">
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
          <template #cell-barbero="{ value }">
            <span class="font-semibold">
                {{ value ?? "No Asignado" }}
            </span>
          </template>

          <template #cell-porcentaje_cita="{ item }">
            {{ item.porcentaje_cita.toFixed(2) * 100 }} %
          </template>
        </DataTable>

      <div v-if="citas.links && citas.links.length > 3" class="mt-4 flex justify-between items-center">
        <div :style="{ color: 'var(--text-secondary)' }">
          Mostrando {{ citas.from }} - {{ citas.to }} de {{ citas.total }}
        </div>
        <div class="flex gap-2">
          <template v-for="link in citas.links" :key="link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'px-3 py-1 rounded',
                link.active ? 'btn-primary' : 'btn-secondary'
              ]"
              v-html="link.label"
            />
            <span
              v-else
              :class="['px-3 py-1 rounded btn-secondary opacity-50 cursor-not-allowed']"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
      </Card>
    </AppLayout>
  </template>
  
  <script setup>
import { ref, computed,onMounted ,watch} from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import DataTable from '@/Components/DataTable.vue';
import { EyeIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';
// Recibe las citas desde el backend
const props = defineProps({
  citas: Object,
});

const statusFilter = ref('');
const searchQuery = ref('');

const page = usePage();

// Ajusta los nombres de columnas según lo que envía el backend
const columns = [
  { key: 'cliente', label: 'Cliente' },
  { key: 'barbero', label: 'Barbero' },
  { key: 'fecha', label: 'Fecha/Hora' },
  { key: 'pago_inicial', label: 'Pago Inicial' },
  { key: 'porcentaje_cita', label: 'Porcentaje' },
  { key: 'estado', label: 'Estado' },
];

// const cancelarCita = (cita) => {
//     Swal.fire({
//       title: '¿Estás seguro?',
//       text: "No podrás revertir esta acción",
//       icon: 'warning',
//       showCancelButton: true,
//       confirmButtonColor: '#EF4444',
//       cancelButtonColor: '#6B7280',
//       confirmButtonText: 'Sí, cancelar cita',
//       cancelButtonText: 'No, mantener',
//       background: 'var(--bg-primary)',
//       color: 'var(--text-primary)'
//     }).then((result) => {
//       if (result.isConfirmed) {
//         router.post(route('citas.cancelar-cita', cita.id));
//       }
//     });
//   };


const actions = [
  {
    label: 'Ver',
    icon:EyeIcon,
    handler: (cita) => router.visit(route('citas-admin.show', cita.id)),    variant: 'primary',
  },
  // {
  //   label: 'Editar',
  //   icon:PencilIcon,
  //   handler: (cita) => router.visit(route('appointments.edit', cita.id)),
  //   variant: 'primary',
  // },
  // {
  //   label: 'Cancelar',
  //   icon:TrashIcon,
  //   handler: cancelarCita,
  //   variant: 'danger',
  // },
];

// Usa los datos paginados del backend
const appointments = computed(() => props.citas.data);

const filteredAppointments = computed(() => {
  let filtered = appointments.value;

  if (statusFilter.value) {
    filtered = filtered.filter(a => a.estado === statusFilter.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(a =>
      (a.cliente || '').toLowerCase().includes(query) ||
      (a.barbero || '').toLowerCase().includes(query)
    );
  }

  return filtered;
});

const getStatusBadgeVariant = (status) => {
  const variants = {
    'pendiente': 'warning',
    'confirmada': 'info',
    'completada': 'success',
    'cancelada': 'danger',
  };
  return variants[status] || 'default';
};

const getStatusLabel = (status) => {
  const labels = {
    'pendiente': 'Pendiente',
    'confirmada': 'Confirmada',
    'completada': 'Completada',
    'cancelada': 'Cancelada',
  };
  return labels[status] || status;
};
onMounted(() => {
    checkFlashMessages();
  });

  // También detectar cambios en las props de la página
  watch(() => page.props.flash, () => {
    checkFlashMessages();
  }, { deep: true });
const checkFlashMessages = () => {
    const flash = page.props.flash;
    
    if (flash?.success) {
      Swal.fire({
        title: '¡Éxito!',
        text: flash.success,
        icon: 'success',
        confirmButtonColor: 'var(--color-primary)',
        background: 'var(--bg-primary)',
        color: 'var(--text-primary)'
      });
    }
    
    if (flash?.error) {
      Swal.fire({
        title: 'Error',
        text: flash.error,
        icon: 'error',
        confirmButtonColor: '#EF4444',
        background: 'var(--bg-primary)',
        color: 'var(--text-primary)'
      });
    }
  };

const formatDateTime = (dateString) => {
  if (!dateString) return '';
  // dateString ya viene formateado desde backend, pero puedes ajustar si lo necesitas
  return dateString;
};
</script>
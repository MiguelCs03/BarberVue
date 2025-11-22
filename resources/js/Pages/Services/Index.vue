<template>
  <Head title="Gestión de Servicios" />

  <AppLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Gestión de Servicios
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Administra los servicios ofrecidos en la barbería
      </p>
    </div>

    <!-- Filters and Actions -->
    <Card class="mb-6">
      <div class="flex flex-col md:flex-row gap-4 justify-between">
        <!-- Search -->
        <div class="flex-1 max-w-md">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar servicios..."
            class="input w-full"
          />
        </div>

        <!-- Create Button -->
        <Link
          :href="route('services.create')"
          class="btn-primary whitespace-nowrap"
        >
          + Nuevo Servicio
        </Link>
      </div>
    </Card>

    <!-- Services Table -->
    <Card>
      <DataTable
        :columns="columns"
        :data="filteredServices"
        :actions="actions"
      >
        <template #cell-precio="{ value }">
          <span class="font-semibold" :style="{ color: 'var(--color-primary)' }">
            Bs. {{ value.toFixed(2) }}
          </span>
        </template>

        <template #cell-duracion_estimada="{ value }">
          <Badge variant="info">
            {{ value }} min
          </Badge>
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

// Static mock data matching database schema
const services = ref([
  {
    id: 1,
    nombre: 'Corte de cabello',
    descripcion: 'Corte clásico de caballero',
    duracion_estimada: 30,
    precio: 15.00,
  },
  {
    id: 2,
    nombre: 'Afeitado',
    descripcion: 'Afeitado con navaja y toalla caliente',
    duracion_estimada: 20,
    precio: 10.00,
  },
  {
    id: 3,
    nombre: 'Corte + Barba',
    descripcion: 'Corte y arreglo de barba',
    duracion_estimada: 45,
    precio: 25.00,
  },
  {
    id: 4,
    nombre: 'Coloración',
    descripcion: 'Tinte y tratamiento',
    duracion_estimada: 60,
    precio: 40.00,
  },
  {
    id: 5,
    nombre: 'Tratamiento capilar',
    descripcion: 'Mascarilla y secado profesional',
    duracion_estimada: 40,
    precio: 20.00,
  },
  {
    id: 6,
    nombre: 'Perfilado de barba',
    descripcion: 'Perfilado detalle',
    duracion_estimada: 15,
    precio: 8.00,
  },
  {
    id: 7,
    nombre: 'Lavado express',
    descripcion: 'Lavado y secado rápido',
    duracion_estimada: 15,
    precio: 6.00,
  },
  {
    id: 8,
    nombre: 'Masaje craneal',
    descripcion: 'Masaje relajante de cuero cabelludo',
    duracion_estimada: 20,
    precio: 12.00,
  },
]);

// Filters
const searchQuery = ref('');

// Table columns
const columns = [
  { key: 'nombre', label: 'Servicio' },
  { key: 'descripcion', label: 'Descripción' },
  { key: 'duracion_estimada', label: 'Duración' },
  { key: 'precio', label: 'Precio' },
];

// Table actions
const actions = [
  {
    label: 'Ver',
    handler: (service) => router.visit(route('services.show', service.id)),
    variant: 'primary',
  },
  {
    label: 'Editar',
    handler: (service) => router.visit(route('services.edit', service.id)),
    variant: 'primary',
  },
  {
    label: 'Eliminar',
    handler: (service) => {
      if (confirm(`¿Estás seguro de eliminar "${service.nombre}"?`)) {
        alert('Funcionalidad de eliminación pendiente (backend)');
      }
    },
    variant: 'danger',
  },
];

// Filtered services
const filteredServices = computed(() => {
  if (!searchQuery.value) return services.value;

  const query = searchQuery.value.toLowerCase();
  return services.value.filter(
    (service) =>
      service.nombre.toLowerCase().includes(query) ||
      service.descripcion.toLowerCase().includes(query)
  );
});
</script>

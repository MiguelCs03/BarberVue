<template>
  <Head title="Gestión de Usuarios" />

  <AppLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Gestión de Usuarios
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Administra propietarios, barberos, secretarias y clientes
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
            placeholder="Buscar por nombre o email..."
            class="input w-full"
          />
        </div>

        <!-- Role Filter -->
        <div class="flex gap-3 items-center">
          <select
            v-model="selectedRole"
            class="input"
          >
            <option value="">Todos los roles</option>
            <option value="propietario">Propietarios</option>
            <option value="barbero">Barberos</option>
            <option value="secretaria">Secretarias</option>
            <option value="cliente">Clientes</option>
          </select>

          <Link
            :href="route('users.create')"
            class="btn-primary whitespace-nowrap"
          >
            + Nuevo Usuario
          </Link>
        </div>
      </div>
    </Card>

    <!-- Users Table -->
    <Card>
      <DataTable
        :columns="columns"
        :data="filteredUsers"
        :actions="actions"
      >
        <template #cell-role="{ value }">
          <Badge :variant="getRoleBadgeVariant(value)">
            {{ getRoleLabel(value) }}
          </Badge>
        </template>

        <template #cell-status="{ value }">
          <Badge :variant="value === 'active' ? 'success' : 'warning'">
            {{ value === 'active' ? 'Activo' : 'Inactivo' }}
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

// Static mock data
const users = ref([
  {
    id: 1,
    name: 'Juan Pérez',
    email: 'juan.perez@barbershop.com',
    phone: '+591 7123-4567',
    role: 'propietario',
    status: 'active',
  },
  {
    id: 2,
    name: 'María González',
    email: 'maria.gonzalez@barbershop.com',
    phone: '+591 7234-5678',
    role: 'barbero',
    status: 'active',
  },
  {
    id: 3,
    name: 'Carlos Rodríguez',
    email: 'carlos.rodriguez@barbershop.com',
    phone: '+591 7345-6789',
    role: 'barbero',
    status: 'active',
  },
  {
    id: 4,
    name: 'Ana Martínez',
    email: 'ana.martinez@barbershop.com',
    phone: '+591 7456-7890',
    role: 'secretaria',
    status: 'active',
  },
  {
    id: 5,
    name: 'Pedro Sánchez',
    email: 'pedro.sanchez@gmail.com',
    phone: '+591 7567-8901',
    role: 'cliente',
    status: 'active',
  },
  {
    id: 6,
    name: 'Laura Torres',
    email: 'laura.torres@gmail.com',
    phone: '+591 7678-9012',
    role: 'cliente',
    status: 'inactive',
  },
]);

// Filters
const searchQuery = ref('');
const selectedRole = ref('');

// Table columns
const columns = [
  { key: 'name', label: 'Nombre' },
  { key: 'email', label: 'Email' },
  { key: 'phone', label: 'Teléfono' },
  { key: 'role', label: 'Rol' },
  { key: 'status', label: 'Estado' },
];

// Table actions
const actions = [
  {
    label: 'Ver',
    handler: (user) => router.visit(route('users.show', user.id)),
    variant: 'primary',
  },
  {
    label: 'Editar',
    handler: (user) => router.visit(route('users.edit', user.id)),
    variant: 'primary',
  },
  {
    label: 'Eliminar',
    handler: (user) => {
      if (confirm(`¿Estás seguro de eliminar a ${user.name}?`)) {
        alert('Funcionalidad de eliminación pendiente (backend)');
      }
    },
    variant: 'danger',
  },
];

// Filtered users based on search and role
const filteredUsers = computed(() => {
  let filtered = users.value;

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (user) =>
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query)
    );
  }

  // Filter by role
  if (selectedRole.value) {
    filtered = filtered.filter((user) => user.role === selectedRole.value);
  }

  return filtered;
});

// Helper functions
const getRoleLabel = (role) => {
  const labels = {
    propietario: 'Propietario',
    barbero: 'Barbero',
    secretaria: 'Secretaria',
    cliente: 'Cliente',
  };
  return labels[role] || role;
};

const getRoleBadgeVariant = (role) => {
  const variants = {
    propietario: 'primary',
    barbero: 'secondary',
    secretaria: 'warning',
    cliente: 'default',
  };
  return variants[role] || 'default';
};
</script>

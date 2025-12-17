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
            <option value="barbero">Barberos</option>
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
        <template #cell-rol="{ value }">
          <Badge :variant="getRoleBadgeVariant(value)">
            {{ getRoleLabel(value) }}
          </Badge>
        </template>

        <template #cell-estado="{ value }">
          <Badge :variant="getEstadoBadgeVariant(value)">
            {{ getEstadoLabel(value) }}
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
import { EyeIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

// Receive users from backend
const props = defineProps({
  users: Array,
});

// Filters
const searchQuery = ref('');
const selectedRole = ref('');

// Table columns
const columns = [
  { key: 'name', label: 'Nombre' },
  { key: 'email', label: 'Email' },
  { key: 'rol', label: 'Rol' },
  { key: 'estado', label: 'Estado' },
  { key: 'created_at', label: 'Fecha Registro' },
];




// Table actions
const actions = [
  {
    label: 'Ver',
    icon:EyeIcon,
    handler: (user) => router.visit(route('users.show', user.id)),
    variant: 'primary',
  },
  {
    label: 'Editar',
    icon:PencilIcon,
    handler: (user) => router.visit(route('users.edit', user.id)),
    variant: 'primary',
  },
  {
    label: 'Eliminar',
    icon:TrashIcon,
    handler: (user) => {
      if (confirm(`¿Estás seguro de eliminar a ${user.name}?`)) {
        router.delete(route('users.destroy', user.id), {
          preserveScroll: true,
        });
      }
    },
    variant: 'danger',
  },
];

// Filtered users based on search and role
const filteredUsers = computed(() => {
  let filtered = props.users;

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
    filtered = filtered.filter((user) => user.rol === selectedRole.value);
  }

  return filtered;
});

// Helper functions
const getRoleLabel = (role) => {
  const labels = {
    barbero: 'Barbero',
    cliente: 'Cliente',
  };
  return labels[role] || role;
};

const getRoleBadgeVariant = (role) => {
  const variants = {
    barbero: 'primary',
    cliente: 'secondary',
  };
  return variants[role] || 'default';
};

const getEstadoLabel = (estado) => {
  const labels = {
    disponible: 'Disponible',
    ocupado: 'Ocupado',
    descanso: 'Descanso',
    activo: 'Activo',
    inactivo: 'Inactivo',
  };
  return labels[estado] || estado;
};

const getEstadoBadgeVariant = (estado) => {
  const variants = {
    disponible: 'success',
    ocupado: 'warning',
    descanso: 'info',
    activo: 'success',
    inactivo: 'danger',
  };
  return variants[estado] || 'default';
};
</script>

<template>
  <Head :title="`Usuario: ${user.name}`" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('users.index')"
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
            {{ user.name }}
          </h1>
          <Badge :variant="getRoleBadgeVariant(user.rol)">
            {{ getRoleLabel(user.rol) }}
          </Badge>
        </div>

        <Link :href="route('users.edit', user.id)" class="btn-primary">
          Editar Usuario
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <Card>
          <h2 class="text-xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Información del Usuario
          </h2>

          <div class="space-y-4">
            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Nombre Completo
              </label>
              <p class="mt-1 text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                {{ user.name }}
              </p>
            </div>

            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Email
              </label>
              <p class="mt-1" :style="{ color: 'var(--text-primary)' }">
                {{ user.email }}
              </p>
            </div>

            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Rol
              </label>
              <div class="mt-1">
                <Badge :variant="getRoleBadgeVariant(user.rol)">
                  {{ getRoleLabel(user.rol) }}
                </Badge>
              </div>
            </div>

            <div v-if="user.rol === 'barbero' && user.estado_barbero">
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Estado del Barbero
              </label>
              <div class="mt-1">
                <Badge :variant="getEstadoBadgeVariant(user.estado_barbero)">
                  {{ getEstadoLabel(user.estado_barbero) }}
                </Badge>
              </div>
            </div>

            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Fecha de Registro
              </label>
              <p class="mt-1" :style="{ color: 'var(--text-primary)' }">
                {{ user.created_at }}
              </p>
            </div>
          </div>
        </Card>
      </div>

      <div class="space-y-6">
        <Card>
          <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Acciones
          </h3>
          <div class="space-y-2">
            <Link :href="route('users.edit', user.id)" class="block btn-primary text-center">
              Editar Usuario
            </Link>
            <button
              @click="handleDelete"
              class="w-full btn-secondary text-left"
              :style="{ color: '#DC2626' }"
            >
              Eliminar Usuario
            </button>
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

const props = defineProps({
  user: Object,
});

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
  };
  return labels[estado] || estado;
};

const getEstadoBadgeVariant = (estado) => {
  const variants = {
    disponible: 'success',
    ocupado: 'warning',
    descanso: 'info',
  };
  return variants[estado] || 'default';
};

const handleDelete = () => {
  if (confirm(`¿Estás seguro de eliminar a ${props.user.name}?`)) {
    router.delete(route('users.destroy', props.user.id));
  }
};
</script>

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
        Volver a la lista
      </Link>

      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">
              {{ user.name }} {{ user.apellido }}
            </h1>
            <Badge :variant="user.estado === 'activo' ? 'success' : 'danger'">
              {{ user.estado === 'activo' ? 'Cuenta Activa' : 'Inactivo' }}
            </Badge>
          </div>
          <p :style="{ color: 'var(--text-secondary)' }">
            Registrado {{ DateHelper.formatRelativeDate(user.created_at) }}
          </p>
        </div>

        <div class="flex gap-2">
          <Link :href="route('users.edit', user.id)" class="btn-primary whitespace-nowrap">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Editar Perfil
          </Link>
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <Card>
        <div class="flex items-center gap-2 mb-6">
          <svg class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">Detalles de Cuenta</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">Correo Electrónico</label>
            <p class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ user.email }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">Teléfono</label>
            <p class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ user.telefono  ?? "N/A" }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">Rol de Sistema</label>
            <Badge :variant="getRoleBadgeVariant(user.rol)">
              {{ getRoleLabel(user.rol) }}
            </Badge>
          </div>

          <div v-if="user.rol === 'barbero'">
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">Estado Laboral</label>
            <Badge :variant="getEstadoBadgeVariant(user.estado_barbero)">
              {{ getEstadoLabel(user.estado_barbero) }}
            </Badge>
          </div>
        </div>
      </Card>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card class="md:col-span-2">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="flex gap-4">
              <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-500/10">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase" :style="{ color: 'var(--text-secondary)' }">Creado el</label>
                <p class="font-medium">{{ DateHelper.formatDate(user.created_at) }}</p>
              </div>
            </div>
            
            <div class="flex gap-4">
              <div class="w-10 h-10 rounded-full flex items-center justify-center bg-amber-500/10">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase" :style="{ color: 'var(--text-secondary)' }">Último Cambio</label>
                <p class="font-medium">{{ DateHelper.formatDate(user.updated_at) }}</p>
              </div>
            </div>
          </div>
        </Card>

        <Card border-color="red">
          <h3 class="text-sm font-bold text-red-600 uppercase mb-4">Zona de Peligro</h3>
          <button
            @click="handleDelete"
            class="w-full py-2 px-4 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white transition-colors text-sm font-bold"
          >
            Eliminar Usuario
          </button>
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
import { DateHelper } from '@/helpers/dateHelper';


const props = defineProps({
  user: Object,
});

// Los métodos de etiquetas se mantienen igual para la lógica de negocio
const getRoleLabel = (role) => {
  const labels = { administrador: 'Admin', barbero: 'Barbero', cliente: 'Cliente' };
  return labels[role] || role;
};

const getRoleBadgeVariant = (role) => {
  const variants = { administrador: 'danger', barbero: 'primary', cliente: 'info' };
  return variants[role] || 'default';
};

const getEstadoLabel = (estado) => {
  const labels = { disponible: '🟢 Disponible', ocupado: '🔴 En Servicio', descanso: '🟡 En Descanso' };
  return labels[estado] || estado;
};

const getEstadoBadgeVariant = (estado) => {
  const variants = { disponible: 'success', ocupado: 'danger', descanso: 'warning' };
  return variants[estado] || 'default';
};

const handleDelete = () => {
  if (confirm(`¿Estás seguro de eliminar a ${props.user.name}? Esta acción no se puede deshacer.`)) {
    router.delete(route('users.destroy', props.user.id));
  }
};
</script>
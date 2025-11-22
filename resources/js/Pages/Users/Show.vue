<template>
  <Head :title="`Usuario: ${user.name}`" />

  <AppLayout>
    <!-- Page Header -->
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

      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
            Detalle del Usuario
          </h1>
          <p :style="{ color: 'var(--text-secondary)' }">
            Información completa del usuario
          </p>
        </div>

        <Link
          :href="route('users.edit', user.id)"
          class="btn-primary"
        >
          Editar Usuario
        </Link>
      </div>
    </div>

    <!-- User Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Info Card -->
      <Card class="lg:col-span-2">
        <div class="flex items-start gap-6 mb-6">
          <!-- Avatar -->
          <div class="w-24 h-24 rounded-full flex items-center justify-center text-5xl flex-shrink-0" :style="{ backgroundColor: 'var(--bg-primary)' }">
            👤
          </div>

          <!-- Basic Info -->
          <div class="flex-1">
            <h2 class="text-2xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
              {{ user.name }}
            </h2>
            <div class="flex gap-2 mb-3">
              <Badge :variant="getRoleBadgeVariant(user.role)">
                {{ getRoleLabel(user.role) }}
              </Badge>
              <Badge :variant="user.status === 'active' ? 'success' : 'warning'">
                {{ user.status === 'active' ? 'Activo' : 'Inactivo' }}
              </Badge>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--text-primary)' }">
            Información de Contacto
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--text-secondary)' }">
                Email
              </label>
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span :style="{ color: 'var(--text-primary)' }">{{ user.email }}</span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--text-secondary)' }">
                Teléfono
              </label>
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span :style="{ color: 'var(--text-primary)' }">{{ user.phone }}</span>
              </div>
            </div>
          </div>
        </div>
      </Card>

      <!-- Stats Card (placeholder for future features) -->
      <Card>
        <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--text-primary)' }">
          Estadísticas
        </h3>

        <div class="space-y-4">
          <div v-if="user.role === 'barbero'">
            <div class="text-3xl font-bold" :style="{ color: 'var(--color-primary)' }">24</div>
            <div class="text-sm" :style="{ color: 'var(--text-secondary)' }">Citas completadas</div>
          </div>

          <div v-if="user.role === 'cliente'">
            <div class="text-3xl font-bold" :style="{ color: 'var(--color-primary)' }">8</div>
            <div class="text-sm" :style="{ color: 'var(--text-secondary)' }">Visitas totales</div>
          </div>

          <div>
            <div class="text-3xl font-bold" :style="{ color: 'var(--color-primary)' }">100%</div>
            <div class="text-sm" :style="{ color: 'var(--text-secondary)' }">Tasa de satisfacción</div>
          </div>
        </div>

        <div class="mt-6 pt-6 border-t" :style="{ borderColor: 'var(--text-secondary)' }">
          <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
            Miembro desde: <strong>Enero 2024</strong>
          </p>
        </div>
      </Card>
    </div>

    <!-- Activity Timeline (placeholder) -->
    <Card class="mt-6">
      <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--text-primary)' }">
        Actividad Reciente
      </h3>

      <div class="space-y-4">
        <div class="flex gap-4">
          <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0" :style="{ backgroundColor: 'var(--color-primary)' }"></div>
          <div class="flex-1">
            <p :style="{ color: 'var(--text-primary)' }">Usuario creado</p>
            <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">15 de Enero, 2024</p>
          </div>
        </div>

        <div class="flex gap-4">
          <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0" :style="{ backgroundColor: 'var(--color-secondary)' }"></div>
          <div class="flex-1">
            <p :style="{ color: 'var(--text-primary)' }">Última actualización de perfil</p>
            <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">20 de Noviembre, 2024</p>
          </div>
        </div>
      </div>
    </Card>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';

// Get user ID from route
const userId = window.location.pathname.split('/').pop();

// Mock user data
const mockUsers = {
  '1': { id: 1, name: 'Juan Pérez', email: 'juan.perez@barbershop.com', phone: '+591 7123-4567', role: 'propietario', status: 'active' },
  '2': { id: 2, name: 'María González', email: 'maria.gonzalez@barbershop.com', phone: '+591 7234-5678', role: 'barbero', status: 'active' },
  '3': { id: 3, name: 'Carlos Rodríguez', email: 'carlos.rodriguez@barbershop.com', phone: '+591 7345-6789', role: 'barbero', status: 'active' },
  '4': { id: 4, name: 'Ana Martínez', email: 'ana.martinez@barbershop.com', phone: '+591 7456-7890', role: 'secretaria', status: 'active' },
  '5': { id: 5, name: 'Pedro Sánchez', email: 'pedro.sanchez@gmail.com', phone: '+591 7567-8901', role: 'cliente', status: 'active' },
  '6': { id: 6, name: 'Laura Torres', email: 'laura.torres@gmail.com', phone: '+591 7678-9012', role: 'cliente', status: 'inactive' },
};

const user = mockUsers[userId] || mockUsers['1'];

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

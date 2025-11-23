<template>
  <Head :title="`Servicio: ${service.nombre}`" />

  <AppLayout>
    <!-- Navegación -->
    <div class="mb-6">
      <Link
        :href="route('services.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver a la lista
      </Link>

      <!-- Header con badge de estado -->
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">
              {{ service.nombre }}
            </h1>
            <Badge :variant="service.estado === 'activo' ? 'success' : 'danger'">
              {{ service.estado === 'activo' ? 'Activo' : 'Inactivo' }}
            </Badge>
          </div>
          <p :style="{ color: 'var(--text-secondary)' }">
            Ultima Actualización {{ formatRelativeDate(service.updated_at) }}
          </p>
        </div>

        <Link :href="route('services.edit', service.id)" class="btn-primary whitespace-nowrap">
          <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Editar Servicio
        </Link>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="space-y-6">
      <!-- Información básica -->
      <Card>
        <div class="flex items-center gap-2 mb-4">
          <svg class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
            Información del Servicio
          </h2>
        </div>

        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                Nombre del Servicio
              </label>
              <p class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                {{ service.nombre }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                Estado
              </label>
              <Badge :variant="service.estado === 'activo' ? 'success' : 'danger'">
                {{ service.estado === 'activo' ? '✓ Activo' : '✕ Inactivo' }}
              </Badge>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                Precio del Servicio
              </label>
              <div class="flex items-baseline gap-1">
                <span class="text-sm font-bold" :style="{ color: 'var(--text-secondary)' }">Bs.</span>
                <span class="text-2xl font-bold" :style="{ color: 'var(--color-primary)' }">
                  {{ Number(service.precio).toFixed(2) }}
                </span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                Duración Estimada
              </label>
              <div class="flex items-center gap-2">
                <svg class="w-6 h-6" :style="{ color: 'var(--color-secondary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex items-baseline gap-1">
                  <span class="text-2xl font-bold" :style="{ color: 'var(--color-secondary)' }">
                    {{ service.duracion_estimada }}
                  </span>
                  <span class="text-sm font-semibold" :style="{ color: 'var(--text-secondary)' }">
                    min
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t" :style="{ borderColor: 'var(--text-secondary)' }">
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
              Descripción
            </label>
            <p class="text-base leading-relaxed" :style="{ color: service.descripcion ? 'var(--text-primary)' : 'var(--text-secondary)' }">
              {{ service.descripcion || 'Sin descripción disponible' }}
            </p>
          </div>
        </div>
      </Card>

      <!-- Información de registro -->
      <Card>
        <div class="flex items-center gap-2 mb-4">
          <svg class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
            Historial de Cambios
          </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="flex gap-4">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{ backgroundColor: 'var(--color-primary)', opacity: 0.1 }">
                <svg class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <label class="block text-xs font-semibold uppercase tracking-wide mb-1" :style="{ color: 'var(--text-secondary)' }">
                Fecha de Creación
              </label>
              <p class="font-medium" :style="{ color: 'var(--text-primary)' }">
                {{ formatDate(service.created_at) }}
              </p>
              <p class="text-sm mt-1" :style="{ color: 'var(--text-secondary)' }">
                {{ formatRelativeDate(service.created_at) }}
              </p>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{ backgroundColor: 'var(--color-secondary)', opacity: 0.1 }">
                <svg class="w-5 h-5" :style="{ color: 'var(--color-secondary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <label class="block text-xs font-semibold uppercase tracking-wide mb-1" :style="{ color: 'var(--text-secondary)' }">
                Última Actualización
              </label>
              <p class="font-medium" :style="{ color: 'var(--text-primary)' }">
                {{ formatDate(service.updated_at) }}
              </p>
              <p class="text-sm mt-1" :style="{ color: 'var(--text-secondary)' }">
                {{ formatRelativeDate(service.updated_at) }}
              </p>
            </div>
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { formatDate, formatRelativeDate } from '@/helpers/dateHelper';
const props = defineProps({
  service: {
    type: Object,
    required: true,
  },
});


</script>
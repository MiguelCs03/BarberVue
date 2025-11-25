<template>
    <Head :title="`Cita #${cita.id}`" />
  
    <AppLayout>
      <!-- Navegación -->
      <div class="mb-6">
        <Link
          :href="route('citas-admin.index')"
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
                Cita #{{ cita.id }}
              </h1>
              <Badge :variant="getStatusBadgeVariant(cita.estado)">
                {{ getStatusLabel(cita.estado) }}
              </Badge>
            </div>
            <p :style="{ color: 'var(--text-secondary)' }">
              {{ cita.fecha }}
            </p>
          </div>
  
          <Link :href="route('citas-admin.edit', cita.id)" class="btn-primary whitespace-nowrap">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Editar Cita
          </Link>
        </div>
      </div>
  
      <!-- Contenido principal -->
      <div class="space-y-6">
        <!-- Información básica -->
        <Card>
            <div class="flex items-center gap-2 mb-4">
                <InformationCircleIcon class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" />
                <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
                    Información de la Cita
                </h2>
            </div>
            
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                  Cliente
                </label>
                <p class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                  {{ cita.cliente.nombre }}
                </p>
              </div>
  
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                  Barbero
                </label>
                <p class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                    {{ cita.barbero?.nombre || 'No asignado' }}
                </p>
              </div>
  
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                  Estado
                </label>
                <Badge :variant="getStatusBadgeVariant(cita.estado)">
                  {{ getStatusLabel(cita.estado) }}
                </Badge>
              </div>
  
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                  Fecha y Hora
                </label>
                <p class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                  {{ cita.fecha }}
                </p>
              </div>
  
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                  Monto Total
                </label>
                <div class="flex items-baseline gap-1">
                  <span class="text-sm font-bold" :style="{ color: 'var(--text-secondary)' }">Bs.</span>
                  <span class="text-2xl font-bold" :style="{ color: 'var(--color-primary)' }">
                    {{ Number(cita.monto_total).toFixed(2)}}
                  </span>
                </div>
              </div>
  
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                  Pago Inicial
                </label>
                <div class="flex items-baseline gap-1">
                  <span class="text-sm font-bold" :style="{ color: 'var(--text-secondary)' }">Bs.</span>
                  <span class="text-2xl font-bold" :style="{ color: 'var(--color-secondary)' }">
                    {{ Number(cita.pago_inicial).toFixed(2) }}
                  </span>
                </div>
              </div>
  
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                  Porcentaje de Adelanto
                </label>
                <p class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                  {{ cita.porcentaje_cita * 100 }}%
                </p>
              </div>
  
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                  Tipo de Pago
                </label>
                <p class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                  {{ cita.tipo_pago.nombre || 'No especificado' }}
                </p>
              </div>
            </div>
  
            <!-- Servicios -->
            <div class="pt-4 border-t" :style="{ borderColor: 'var(--text-secondary)' }">
                <div class="flex items-center gap-2 mb-3">
                    <ScissorsIcon class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" />
                    <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
                        Servicios Contratados
                    </h2>
                </div>
              <div class="space-y-2">
                <div 
                  v-for="servicio in cita.servicios" 
                  :key="servicio.id"
                  class="flex justify-between items-center p-3 rounded-lg"
                  :style="{ backgroundColor: 'var(--bg-secondary)' }"
                >
                  <span class="font-medium" :style="{ color: 'var(--text-primary)' }">
                    {{ servicio.nombre }}
                  </span>
                  <span class="font-bold" :style="{ color: 'var(--color-primary)' }">
                    Bs. {{ Number(servicio.precio).toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </Card>
        <Card>
        <div class="flex items-center gap-2 mb-4">
            <ClockIcon class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" />
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
                {{ cita.created_at }}
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
                {{ cita.updated_at }}
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
  import { 
  InformationCircleIcon, 
  ClockIcon, 
  ScissorsIcon 
} from '@heroicons/vue/24/outline';

  const props = defineProps({
    cita: {
      type: Object,
      required: true,
    },
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
  </script>
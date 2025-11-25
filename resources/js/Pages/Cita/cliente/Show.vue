<template>
    <Head :title="`Mi Cita #${cita.id}`" />
  
    <AppLayout>
      <!-- Navegación -->
      <div class="mb-6">
        <Link
          :href="route('citas-cliente.index')"
          class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
          :style="{ color: 'var(--color-primary)' }"
        >
            <ArrowLeftIcon class="w-5 h-5" />
            Volver a Mis Citas
        </Link>
  
        <!-- Header con badge de estado -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">
                Mi Cita #{{ cita.id }}
              </h1>
              <Badge :variant="getStatusBadgeVariant(cita.estado)">
                {{ getStatusLabel(cita.estado) }}
              </Badge>
            </div>
            <p :style="{ color: 'var(--text-secondary)' }">
              Fecha de reserva {{ cita.fecha }}
            </p>
          </div>
         
          <button 
           v-if="cita.estado !== 'completada' && cita.estado !== 'cancelada'"   
            @click="cancelarCita"
            class="inline-flex items-center gap-2 px-4 py-2 rounded hover:opacity-75 transition-opacity"
            :style="{ 
                backgroundColor: '#EF4444',
                color: 'white'
            }"
            >
            <XMarkIcon class="w-5 h-5" />
            <span>Cancelar Cita</span>
            </button>
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
                  Barbero Asignado
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
                    {{ Number(cita.monto_total).toFixed(2) }}
                  </span>
                </div>
              </div>
  
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                  Adelanto Pagado
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
  
        <!-- Historial -->
        <Card>
        <div class="flex items-center gap-2 mb-4">
          <ClockIcon class="w-6 h-6" :style="{ color: 'var(--color-primary)' }" />
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
            Historial de la Cita
          </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="flex gap-4">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{ backgroundColor: 'var(--color-primary)', opacity: 0.1 }">
                <CalendarDaysIcon class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" />
              </div>
            </div>
            <div class="flex-1">
              <label class="block text-xs font-semibold uppercase tracking-wide mb-1" :style="{ color: 'var(--text-secondary)' }">
                Fecha de Reserva
              </label>
              <p class="font-medium" :style="{ color: 'var(--text-primary)' }">
                {{ cita.created_at }}
              </p>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{ backgroundColor: 'var(--color-secondary)', opacity: 0.1 }">
                <ArrowPathIcon class="w-5 h-5" :style="{ color: 'var(--color-secondary)' }" />
              </div>
            </div>
            <div class="flex-1">
              <label class="block text-xs font-semibold uppercase tracking-wide mb-1" :style="{ color: 'var(--text-secondary)' }">
                Última Modificación
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
  import { Head, Link, router, usePage } from '@inertiajs/vue3';
  import { onMounted, watch } from 'vue';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import Card from '@/Components/Card.vue';
  import Badge from '@/Components/Badge.vue';
  import { 
    InformationCircleIcon, 
    ClockIcon, 
    ScissorsIcon,
    ArrowLeftIcon,
    XMarkIcon,
    CalendarDaysIcon,
    ArrowPathIcon
  } from '@heroicons/vue/24/outline';
  import Swal from 'sweetalert2';
  
  const props = defineProps({
    cita: {
      type: Object,
      required: true,
    },
  });

  const page = usePage();
  
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
  
  const cancelarCita = () => {
    Swal.fire({
      title: '¿Estás seguro?',
      text: "No podrás revertir esta acción",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#EF4444',
      cancelButtonColor: '#6B7280',
      confirmButtonText: 'Sí, cancelar cita',
      cancelButtonText: 'No, mantener',
      background: 'var(--bg-primary)',
      color: 'var(--text-primary)'
    }).then((result) => {
      if (result.isConfirmed) {
        router.post(route('citas.cancelar-cita', props.cita.id));
      }
    });
  };

  // Detectar mensajes flash al montar el componente
  onMounted(() => {
    checkFlashMessages();
  });

  
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
  </script>
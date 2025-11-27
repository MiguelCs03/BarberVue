<template>
  <Head title="Pago de Reserva" />

  <AppLayout>
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
          Completa tu Pago
        </h1>
        <p class="text-lg" :style="{ color: 'var(--text-secondary)' }">
          Escanea el código QR para confirmar tu reserva
        </p>
      </div>

      <!-- Main Card -->
      <Card>
        <div class="space-y-8">
          <!-- QR Code Section -->
          <div class="flex flex-col items-center">
            <div class="mb-6">
              <div 
                class="p-6 rounded-2xl shadow-xl"
                :style="{ backgroundColor: 'white' }"
              >
                <img 
                  :src="'data:image/png;base64,' + cita.qrImage" 
                  alt="Código QR de Pago"
                  class="w-64 h-64 md:w-80 md:h-80"
                />
              </div>
            </div>

            <!-- Payment Info -->
            <div class="text-center mb-6">
              <p class="text-sm font-semibold uppercase tracking-wide mb-2" :style="{ color: 'var(--text-secondary)' }">
                Monto a Pagar
              </p>
              <div class="flex items-baseline justify-center gap-2">
                <span class="text-2xl font-bold" :style="{ color: 'var(--text-secondary)' }">Bs.</span>
                <span class="text-5xl font-bold" :style="{ color: 'var(--color-primary)' }">
                  {{ Number(cita.monto).toFixed(2) }}
                </span>
              </div>
            </div>

            <!-- Instructions -->
            <div class="w-full max-w-md space-y-4">
              <div class="p-4 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)' }">
                <h3 class="font-bold mb-3 flex items-center gap-2" :style="{ color: 'var(--text-primary)' }">
                  <InformationCircleIcon class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" />
                  Instrucciones de Pago
                </h3>
                <ol class="space-y-2 text-sm" :style="{ color: 'var(--text-secondary)' }">
                  <li class="flex gap-2">
                    <span class="font-bold">1.</span>
                    <span>Abre tu aplicación bancaria o billetera digital</span>
                  </li>
                  <li class="flex gap-2">
                    <span class="font-bold">2.</span>
                    <span>Selecciona la opción de pago por QR</span>
                  </li>
                  <li class="flex gap-2">
                    <span class="font-bold">3.</span>
                    <span>Escanea el código QR mostrado arriba</span>
                  </li>
                  <li class="flex gap-2">
                    <span class="font-bold">4.</span>
                    <span>Confirma el pago en tu aplicación</span>
                  </li>
                </ol>
              </div>

              <!-- Appointment Details -->
              <div class="p-4 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)' }">
                <h3 class="font-bold mb-3 flex items-center gap-2" :style="{ color: 'var(--text-primary)' }">
                  <CalendarDaysIcon class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" />
                  Detalles de la Cita
                </h3>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span :style="{ color: 'var(--text-secondary)' }">Fecha y Hora:</span>
                    <span class="font-semibold" :style="{ color: 'var(--text-primary)' }">{{ cita.fecha }}</span>
                  </div>
                  <div v-if="cita.cliente" class="flex justify-between">
                    <span :style="{ color: 'var(--text-secondary)' }">Cliente:</span>
                    <span class="font-semibold" :style="{ color: 'var(--text-primary)' }">{{ cita.cliente }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Status -->
          <div class="border-t pt-6" :style="{ borderColor: 'var(--text-secondary)' }">
            <div v-if="!paymentConfirmed" class="text-center">
              <div class="flex items-center justify-center gap-3 mb-4">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2" :style="{ borderColor: 'var(--color-primary)' }"></div>
                <p class="font-medium" :style="{ color: 'var(--text-secondary)' }">
                  Esperando confirmación de pago...
                </p>
              </div>
              <button
                @click="checkPaymentStatus"
                class="px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-opacity"
                :style="{ 
                  backgroundColor: 'var(--color-primary)',
                  color: 'white'
                }"
              >
                Ya Pagué - Verificar Estado
              </button>
            </div>

            <div v-else class="text-center">
              <div class="flex items-center justify-center gap-3 mb-4">
                <CheckCircleIcon class="w-8 h-8" :style="{ color: '#10B981' }" />
                <p class="text-xl font-bold" :style="{ color: '#10B981' }">
                  ¡Pago Confirmado!
                </p>
              </div>
              <p class="mb-4" :style="{ color: 'var(--text-secondary)' }">
                Tu reserva ha sido confirmada exitosamente
              </p>
              <Link
                :href="route('citas-cliente.index')"
                class="inline-block px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-opacity"
                :style="{ 
                  backgroundColor: 'var(--color-primary)',
                  color: 'white'
                }"
              >
                Ver Mis Citas
              </Link>
            </div>
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import { 
  InformationCircleIcon,
  CalendarDaysIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  cita: {
    type: Object,
    required: true,
  },
});

const paymentConfirmed = ref(false);
let pollingInterval = null;

// Verificar el estado del pago
const checkPaymentStatus = async () => {
  try {
    const response = await axios.get(`/api/citas/${props.cita.id}/verificar-pago`);
    
    if (response.data.confirmada) {
      paymentConfirmed.value = true;
      
      // Detener el polling
      if (pollingInterval) {
        clearInterval(pollingInterval);
      }

      // Mostrar mensaje de éxito
      Swal.fire({
        title: '¡Pago Confirmado!',
        text: 'Tu reserva ha sido confirmada exitosamente',
        icon: 'success',
        confirmButtonColor: 'var(--color-primary)',
        background: 'var(--bg-primary)',
        color: 'var(--text-primary)'
      });
    }
  } catch (error) {
    console.error('Error al verificar estado de pago:', error);
  }
};

// Iniciar polling automático cada 5 segundos
onMounted(() => {
  // Verificar inmediatamente
  checkPaymentStatus();
  
  // Luego verificar cada 5 segundos
  pollingInterval = setInterval(() => {
    checkPaymentStatus();
  }, 5000);
});

// Limpiar el intervalo cuando el componente se desmonte
onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});
</script>

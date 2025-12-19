<template>
  <Head title="Detalle de Venta" />

  <AppLayout>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <Link
          :href="route('ventas.index')"
          class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
          :style="{ color: 'var(--color-primary)' }"
        >
          <ArrowLeftIcon class="w-5 h-5" />
          Volver al listado
        </Link>
        <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
          Venta #{{ venta.id }}
        </h1>
        <p :style="{ color: 'var(--text-secondary)' }">
          Registrada el {{ venta.fecha }}
        </p>
      </div>

      <div class="flex gap-3">
        <button 
          v-if="venta.estado_pago !== 'completado'"
          @click="showPaymentModal = true"
          class="btn-primary px-4 py-2 flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" />
          <span>Registrar Pago</span>
        </button>
        <button 
          @click="confirmDelete"
          class="btn-secondary text-red-500 border-red-200 hover:bg-red-50 dark:border-red-900/30 dark:hover:bg-red-900/20 px-4 py-2 flex items-center gap-2"
        >
          <TrashIcon class="w-5 h-5" />
          <span>Anular Venta</span>
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <Card title="Detalle de Items">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead>
                <tr class="border-b dark:border-gray-700 text-xs font-bold uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
                  <th class="py-3 px-4">Descripción</th>
                  <th class="py-3 px-4 text-center">Tipo</th>
                  <th class="py-3 px-4 text-center">Cant.</th>
                  <th class="py-3 px-4 text-right">Precio</th>
                  <th class="py-3 px-4 text-right">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y dark:divide-gray-700">
                <tr v-for="(item, index) in venta.detalles" :key="index" class="text-sm">
                  <td class="py-4 px-4 font-medium" :style="{ color: 'var(--text-primary)' }">
                    {{ item.nombre }}
                  </td>
                  <td class="py-4 px-4 text-center">
                    <Badge :variant="item.tipo === 'Producto' ? 'info' : 'warning'">
                      {{ item.tipo }}
                    </Badge>
                  </td>
                  <td class="py-4 px-4 text-center">{{ item.cantidad }}</td>
                  <td class="py-4 px-4 text-right">Bs. {{ item.precio_unitario }}</td>
                  <td class="py-4 px-4 text-right font-bold">Bs. {{ item.subtotal }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </Card>

        <Card title="Historial de Pagos">
          <div class="space-y-3">
            <div v-for="(pago, index) in venta.pagos" :key="index" class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
              <div>
                <p class="font-bold" :style="{ color: 'var(--text-primary)' }">{{ pago.metodo }}</p>
                <p class="text-xs text-gray-400">{{ pago.fecha }}</p>
              </div>
              <span class="font-bold text-green-600 dark:text-green-400">Bs. {{ pago.monto }}</span>
            </div>
            
            <div v-if="venta.pagos.length === 0" class="text-center py-4 text-gray-400 italic">
              No se han registrado pagos para esta venta.
            </div>
          </div>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <Card title="Información">
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-secondary)' }">Cliente</label>
              <p class="font-bold flex items-center gap-2" :style="{ color: 'var(--text-primary)' }">
                <UserIcon class="w-4 h-4" />
                {{ venta.cliente }}
              </p>
            </div>
            <div v-if="venta.barbero !== 'N/A'">
              <label class="block text-xs font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-secondary)' }">Atendido por</label>
              <p class="font-bold flex items-center gap-2" :style="{ color: 'var(--text-primary)' }">
                <ScissorsIcon class="w-4 h-4" />
                {{ venta.barbero }}
              </p>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-secondary)' }">Estado de Pago</label>
              <Badge :variant="getBadgeVariant(venta.estado_pago)" class="mt-1 uppercase text-[10px] font-bold">
                {{ venta.estado_pago }}
              </Badge>
            </div>
          </div>
        </Card>

        <Card title="Resumen Económico">
          <div class="space-y-4">
            <div class="flex justify-between text-sm">
              <span :style="{ color: 'var(--text-secondary)' }">Monto Total</span>
              <span class="font-bold">Bs. {{ venta.monto_total }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span :style="{ color: 'var(--text-secondary)' }">Total Pagado</span>
              <span class="font-bold text-green-600">Bs. {{ venta.total_pagado }}</span>
            </div>
            
            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
              <div 
                class="bg-green-500 h-full transition-all duration-500" 
                :style="{ width: (venta.total_pagado / venta.monto_total * 100) + '%' }"
              ></div>
            </div>

            <div class="flex justify-between text-2xl font-bold border-t pt-4" :style="{ color: 'var(--text-primary)' }">
              <span>Restante</span>
              <span :class="venta.restante > 0 ? 'text-amber-500' : 'text-green-600'">Bs. {{ venta.restante }}</span>
            </div>
          </div>
        </Card>
      </div>
    </div>

    <!-- Modal Registrar Pago -->
    <Modal :show="showPaymentModal" @close="closePaymentModal" maxWidth="md">
      <div class="p-6">
        <h2 class="text-xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">Registrar Pago</h2>
        
        <form @submit.prevent="handlePaymentSubmission">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-bold mb-2">Método de Pago</label>
              <select v-model="paymentForm.tipo_pago_id" class="input w-full py-2" required>
                <option v-for="t in tiposPago" :key="t.id" :value="t.id">{{ t.nombre }}</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-bold mb-2">Monto a Pagar</label>
              <input 
                v-model.number="paymentForm.monto" 
                type="number" 
                step="0.01" 
                :max="venta.restante"
                class="input w-full py-2 font-bold text-lg" 
                required 
              />
              <p class="text-xs text-gray-500 mt-1">Saldo pendiente máximo: Bs. {{ venta.restante }}</p>
            </div>

            <div class="flex justify-end gap-3 mt-6">
              <button 
                type="button" 
                @click="closePaymentModal"
                class="btn-secondary px-4 py-2"
              >
                Cancelar
              </button>
              <button 
                type="submit" 
                :disabled="paymentForm.processing || paymentForm.monto <= 0"
                class="btn-primary px-6 py-2 flex items-center gap-2"
              >
                <ArrowPathIcon v-if="paymentForm.processing" class="w-5 h-5 animate-spin" />
                <span>Registrar Pago</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </Modal>
    
    <!-- Modal QR -->
    <Modal :show="showQrModal" @close="closeQrModal" maxWidth="sm">
      <div class="p-6 text-center">
        <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">Escanea para Pagar</h3>
        
        <div v-if="qrLoading" class="flex justify-center py-8">
          <ArrowPathIcon class="w-10 h-10 animate-spin text-indigo-500" />
        </div>
        
        <div v-else-if="qrImage" class="space-y-4">
          <div class="bg-white p-4 rounded-xl shadow-lg inline-block">
            <img :src="'data:image/png;base64,' + qrImage" alt="QR Code" class="w-64 h-64" />
          </div>
          <p class="font-bold text-2xl" :style="{ color: 'var(--color-primary)' }">Bs. {{ paymentForm.monto }}</p>
          
          <div class="flex items-center justify-center gap-2 text-sm animate-pulse" :style="{ color: 'var(--text-secondary)' }">
            <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
            Esperando confirmación...
          </div>
        </div>

        <button @click="closeQrModal" class="mt-6 btn-secondary w-full py-2">
          Cerrar
        </button>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import Modal from '@/Components/Modal.vue';
import { 
  ArrowLeftIcon, 
  TrashIcon,
  UserIcon,
  ScissorsIcon,
  PlusIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  venta: Object,
  tiposPago: Array,
});

const showPaymentModal = ref(false);
const showQrModal = ref(false);
const qrImage = ref(null);
const qrLoading = ref(false);
const currentTransactionUuid = ref(null);
let pollingInterval = null;

const paymentForm = useForm({
  tipo_pago_id: props.tiposPago[0]?.id || '',
  monto: props.venta.restante,
});

const getBadgeVariant = (estado) => {
  if (estado === 'completado') return 'success';
  if (estado === 'parcial') return 'warning';
  return 'danger';
};

const confirmDelete = () => {
  if (confirm('¿Estás seguro de anular esta venta? El stock de los productos será devuelto.')) {
    router.delete(route('ventas.destroy', props.venta.id));
  }
};

const handlePaymentSubmission = async () => {
  const selectedPayment = props.tiposPago.find(t => t.id === paymentForm.tipo_pago_id);
  
  if (selectedPayment && selectedPayment.nombre.toLowerCase().includes('qr')) {
    // Lógica para Pago QR
    showPaymentModal.value = false;
    showQrModal.value = true;
    qrLoading.value = true;

    try {
      // 1. Solicitar generación de QR
      const response = await axios.post(route('ventas.payment', props.venta.id), {
        ...paymentForm.data(),
        wants_qr: true // Flag para indicar que queremos JSON con QR, no redirect
      });

      if (response.data.qrImage) {
        qrImage.value = response.data.qrImage;
        currentTransactionUuid.value = response.data.uuid;
        qrLoading.value = false;
        
        // 2. Iniciar Polling
        startPolling();
      }
    } catch (error) {
      console.error('Error generando QR:', error);
      closeQrModal();
      Swal.fire('Error', 'No se pudo generar el código QR', 'error');
    }
  } else {
    // Flujo normal (Efectivo/Stripe)
    paymentForm.post(route('ventas.payment', props.venta.id), {
      onSuccess: () => {
        closePaymentModal();
        Swal.fire('¡Pago Exitoso!', 'El pago ha sido registrado.', 'success');
      },
      onError: () => {
        Swal.fire('Error', 'Hubo un problema al registrar el pago.', 'error');
      }
    });
  }
};

const startPolling = () => {
  if (pollingInterval) clearInterval(pollingInterval);
  
  pollingInterval = setInterval(async () => {
    try {
      const response = await axios.get(route('api.ventas.verificar-pago', {
        uuid: currentTransactionUuid.value
      }));

      if (response.data.confirmada) {
        clearInterval(pollingInterval);
        closeQrModal();
        Swal.fire('¡Pago Confirmado!', 'La transacción se completó exitosamente.', 'success').then(() => {
          router.reload(); // Recargar para ver el nuevo estado
        });
      }
    } catch (error) {
      console.error('Error verificando pago:', error);
    }
  }, 5000);
};

const closePaymentModal = () => {
  showPaymentModal.value = false;
  paymentForm.reset('monto');
};

const closeQrModal = () => {
  showQrModal.value = false;
  qrImage.value = null;
  if (pollingInterval) clearInterval(pollingInterval);
};

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval);
});
</script>

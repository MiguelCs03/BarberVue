<template>
  <Head title="Registrar Venta" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('ventas.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <ArrowLeftIcon class="w-5 h-5" />
        Volver al listado
      </Link>

      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Nueva Venta
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Registre una nueva venta de productos o servicios.
      </p>
    </div>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Items and Form -->
        <div class="lg:col-span-2 space-y-6">
          <Card title="Información General">
            <div class="grid grid-cols-1 gap-4">
              <div>
                <label class="block text-sm font-bold mb-2">Cliente <span class="text-red-500">*</span></label>
                <select v-model="form.cliente_id" class="input w-full py-2">
                  <option value="" disabled>Seleccione un cliente...</option>
                  <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <p v-if="form.errors.cliente_id" class="text-red-500 text-xs mt-1">{{ form.errors.cliente_id }}</p>
              </div>
            </div>
          </Card>

          <Card title="Productos y Servicios">
            <!-- Programación (Solo si hay servicios) -->
            <div v-if="hasServices" class="mb-4 p-4 text-center bg-primary/5 rounded-2xl border border-dashed border-primary/20">
              <p class="text-xs font-bold text-primary uppercase tracking-widest flex items-center justify-center gap-2">
                <ClockIcon class="w-4 h-4" />
                Venta con Servicios: Configure los horarios individuales abajo
              </p>
            </div>

            <!-- Listado de Items -->
            <div class="space-y-6">
              <div v-for="(item, index) in form.items" :key="index" class="p-4 rounded-2xl border bg-white dark:bg-gray-800/20 dark:border-gray-700 shadow-sm transition-all">
                <div class="flex flex-wrap md:flex-nowrap gap-4 items-end">
                  <div class="w-full md:w-32">
                    <label class="block text-xs font-bold mb-1 uppercase tracking-wider opacity-60">Tipo</label>
                    <select v-model="item.type" @change="resetItem(index)" class="input w-full py-2 text-sm">
                      <option value="producto">Producto</option>
                      <option value="servicio">Servicio</option>
                    </select>
                  </div>

                  <!-- Programación Toggle (Solo para servicios) -->
                  <div v-if="item.type === 'servicio'" class="w-full md:w-auto self-center pt-5">
                    <button 
                      type="button" 
                      @click="toggleSchedule(index)"
                      class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition-all border"
                      :class="itemStates[index].selectedHora 
                        ? 'bg-green-50 text-green-600 border-green-200 dark:bg-green-900/20 dark:border-green-800' 
                        : 'bg-primary/5 text-primary border-primary/20 hover:bg-primary/10'"
                    >
                      <CalendarDaysIcon class="w-4 h-4" />
                      {{ itemStates[index].selectedHora ? 'Programado: ' + itemStates[index].selectedHora : 'Programar' }}
                    </button>
                  </div>

                  <div class="w-full md:flex-1">
                    <label class="block text-xs font-bold mb-1 uppercase tracking-wider opacity-60">{{ item.type === 'producto' ? 'Producto' : 'Servicio' }}</label>
                    <select v-model="item.id" @change="updateItemPrice(index)" class="input w-full py-2 text-sm" :disabled="item.type === 'servicio' && !item.barbero_id">
                      <option value="" disabled>Seleccione...</option>
                      <template v-if="item.type === 'producto'">
                        <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.nombre }} (Stock: {{ p.stock_actual }})</option>
                      </template>
                      <template v-else>
                        <option v-for="s in getFilteredServicios(item.barbero_id)" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                      </template>
                    </select>
                  </div>

                  <!-- Barbero Selector (Solo para servicios) -->
                  <div v-if="item.type === 'servicio'" class="w-full md:w-48">
                    <label class="block text-xs font-bold mb-1 uppercase tracking-wider opacity-60">Barbero</label>
                    <select v-model="item.barbero_id" @change="item.id = ''; item.precio = 0" class="input w-full py-2 text-sm" :disabled="!itemStates[index].selectedHora">
                      <option value="" disabled>{{ itemStates[index].loadingBarberos ? 'Cargando...' : 'Seleccione...' }}</option>
                      <option v-for="b in itemStates[index].barberosDisponibles" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                  </div>

                  <div class="w-1/2 md:w-20">
                    <label class="block text-xs font-bold mb-1 uppercase tracking-wider opacity-60">Cant.</label>
                    <input v-model.number="item.cantidad" type="number" min="1" class="input w-full py-2 text-sm" />
                  </div>
                  <div class="w-1/2 md:w-24">
                    <label class="block text-xs font-bold mb-1 uppercase tracking-wider opacity-60">Precio</label>
                    <input v-model.number="item.precio" type="number" readonly class="input w-full py-2 text-sm bg-gray-50/50 dark:bg-gray-800/10" />
                  </div>
                  <div class="w-full md:w-auto">
                    <button type="button" @click="removeItem(index)" class="p-2.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                      <TrashIcon class="w-5 h-5" />
                    </button>
                  </div>
                </div>

                <!-- Collapsible Schedule Section -->
                <transition
                  enter-active-class="transition duration-300 ease-out"
                  enter-from-class="transform -translate-y-4 opacity-0"
                  enter-to-class="transform translate-y-0 opacity-100"
                  leave-active-class="transition duration-200 ease-in"
                  leave-from-class="transform translate-y-0 opacity-100"
                  leave-to-class="transform -translate-y-4 opacity-0"
                >
                  <div v-if="item.type === 'servicio' && itemStates[index].showSchedule" class="mt-6 pt-6 border-t dark:border-gray-700">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-2xl">
                      <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2 text-primary">
                          <ClockIcon class="w-4 h-4" />
                          <h4 class="text-xs font-bold uppercase tracking-widest">Configurar Horario del Servicio</h4>
                        </div>
                        <button type="button" @click="toggleSchedule(index)" class="text-xs font-bold hover:underline opacity-60">Ocultar</button>
                      </div>

                      <div class="scale-90 origin-top">
                        <DateCarousel
                          v-model="itemStates[index].selectedDate"
                          :current-week-start="itemStates[index].currentWeekStart"
                          @previous-week="previousWeek(index)"
                          @next-week="nextWeek(index)"
                        />

                        <TurnoSelector 
                          v-model="itemStates[index].selectedTurno" 
                          :selected-date="itemStates[index].selectedDate"
                        />

                        <HoursCarousel
                          v-if="itemStates[index].selectedDate && itemStates[index].selectedTurno"
                          v-model="itemStates[index].selectedHora"
                          :available-hours="getAvailableHoursForItem(index)"
                        />
                      </div>
                    </div>
                  </div>
                </transition>
              </div>
              <button type="button" @click="addItem" class="btn-secondary w-full py-2 flex items-center justify-center gap-2 border-dashed">
                <PlusIcon class="w-4 h-4" />
                <span>Agregar Item</span>
              </button>
              <p v-if="form.errors.items" class="text-red-500 text-xs mt-1">{{ form.errors.items }}</p>
            </div>
          </Card>

          <Card title="Pagos">
            <div class="space-y-4">
              <div v-for="(pago, index) in form.pagos" :key="index" class="flex gap-4 items-end">
                <div class="flex-1">
                  <label class="block text-xs font-bold mb-1">Método de Pago</label>
                  <select v-model="pago.tipo_pago_id" class="input w-full py-1.5 text-sm">
                    <option v-for="t in tiposPago" :key="t.id" :value="t.id">{{ t.nombre }}</option>
                  </select>
                </div>
                <div class="w-40">
                  <label class="block text-xs font-bold mb-1">Monto</label>
                  <input v-model.number="pago.monto" type="number" class="input w-full py-1.5 text-sm" />
                </div>
                <button v-if="form.pagos.length > 1" type="button" @click="removePago(index)" class="p-2 text-red-500 rounded-lg">
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>
              <button type="button" @click="addPago" class="text-sm font-bold text-primary hover:underline flex items-center gap-1">
                <PlusIcon class="w-4 h-4" /> Agregar otro método
              </button>
              <p v-if="form.errors.pagos" class="text-red-500 text-xs mt-1">{{ form.errors.pagos }}</p>
            </div>
          </Card>
        </div>

        <!-- Right Column: Summary -->
        <div class="space-y-6">
          <Card title="Resumen de Venta">
            <div class="space-y-4">
              <div class="flex justify-between text-sm">
                <span :style="{ color: 'var(--text-secondary)' }">Subtotal</span>
                <span class="font-bold">Bs. {{ subtotal }}</span>
              </div>
              <div class="flex justify-between text-xl font-bold border-t pt-4">
                <span :style="{ color: 'var(--text-primary)' }">Total</span>
                <span class="text-green-600 dark:text-green-400">Bs. {{ subtotal }}</span>
              </div>
              
              <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl space-y-2">
                <div class="flex justify-between text-sm">
                  <span>Monto Pagado</span>
                  <span class="font-bold">Bs. {{ totalPagado }}</span>
                </div>
                <div class="flex justify-between text-sm" :class="restante !== 0 ? 'text-red-500' : 'text-green-500'">
                  <span>Restante</span>
                  <span class="font-bold">Bs. {{ restante }}</span>
                </div>
              </div>

              <button
                type="submit"
                :disabled="form.processing || form.items.length === 0"
                class="btn-primary w-full py-4 flex items-center justify-center gap-3 shadow-lg"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing || form.items.length === 0 }"
              >
                <CloudArrowUpIcon v-if="!form.processing" class="w-6 h-6" />
                <ArrowPathIcon v-else class="w-6 h-6 animate-spin" />
                <span class="text-lg font-bold">
                  {{ form.processing ? 'Registrando...' : 'Confirmar Venta' }}
                </span>
              </button>
              <p v-if="restante > 0" class="text-center text-xs text-amber-500 font-medium">
                La venta se registrará con un saldo pendiente de Bs. {{ restante }}
              </p>
              <p v-else-if="restante < 0" class="text-center text-xs text-red-500 font-medium">
                El monto pagado excede el total de la venta
              </p>
            </div>
          </Card>
        </div>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import DateCarousel from '@/Pages/Cita/administrador/components/DateCarousel.vue';
import TurnoSelector from '@/Pages/Cita/administrador/components/TurnoSelector.vue';
import HoursCarousel from '@/Pages/Cita/administrador/components/HoursCarousel.vue';
import { 
  ArrowPathIcon,
  CalendarDaysIcon,
  ClockIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  clientes: Array,
  productos: Array,
  servicios: Array,
  tiposPago: Array,
  barberos: Array,
});

const now = new Date();
const currentFecha = now.toISOString().split('T')[0];
const currentHora = now.toTimeString().split(' ')[0].substring(0, 5);

// Item UI State (State for each item row)
const itemStates = ref([
  {
    showSchedule: false,
    selectedDate: { date: currentFecha },
    selectedTurno: null,
    selectedHora: null,
    barberosDisponibles: [],
    loadingBarberos: false,
    currentWeekStart: new Date()
  }
]);

const hasServices = computed(() => {
  return form.items.some(item => item.type === 'servicio');
});

const form = useForm({
  cliente_id: '',
  barbero_id: '', 
  fecha: currentFecha,
  hora: currentHora,
  monto_total: 0,
  items: [
    { type: 'producto', id: '', cantidad: 1, precio: 0, barbero_id: '', fecha: currentFecha, hora: currentHora }
  ],
  pagos: [
    { tipo_pago_id: props.tiposPago[0]?.id || '', monto: 0 }
  ]
});

// Row Specific Handlers
const toggleSchedule = (index) => {
  itemStates.value[index].showSchedule = !itemStates.value[index].showSchedule;
};

const createItemState = () => ({
  showSchedule: false,
  selectedDate: { date: currentFecha },
  selectedTurno: null,
  selectedHora: null,
  barberosDisponibles: [],
  loadingBarberos: false,
  currentWeekStart: new Date()
});

// Watchers for Item Schedules
watch(itemStates, (newVal) => {
  newVal.forEach((state, index) => {
    const item = form.items[index];
    if (state.selectedDate && item.fecha !== state.selectedDate.date) {
      item.fecha = state.selectedDate.date;
      state.selectedHora = null; // Reset hour when date changes
      state.barberosDisponibles = [];
    }
    
    if (state.selectedHora && item.hora !== state.selectedHora) {
      item.hora = state.selectedHora;
      cargarBarberosDisponiblesForRow(index);
    } else if (!state.selectedHora && item.hora) {
      // If hour was cleared
      item.hora = '';
      state.barberosDisponibles = [];
    }
  });
}, { deep: true });

const cargarBarberosDisponiblesForRow = async (index) => {
  const item = form.items[index];
  const state = itemStates.value[index];
  
  if (!item.fecha || !item.hora) return;
  
  state.loadingBarberos = true;
  try {
    const response = await axios.post(route('barberos-disponibles-admin'), {
      cliente_id: form.cliente_id || null,
      fecha: item.fecha,
      hora: item.hora
    });
    
    state.barberosDisponibles = response.data.barberos || [];
    
    // Validate current selection
    if (item.barbero_id && !state.barberosDisponibles.some(b => b.id === item.barbero_id)) {
      item.barbero_id = '';
      item.id = '';
      item.precio = 0;
    }
  } catch (error) {
    console.error(`Error al cargar barberos para la fila ${index}:`, error);
  } finally {
    state.loadingBarberos = false;
  }
};

const previousWeek = (index) => {
  const state = itemStates.value[index];
  const newDate = new Date(state.currentWeekStart);
  newDate.setDate(newDate.getDate() - 7);
  state.currentWeekStart = newDate;
};

const nextWeek = (index) => {
  const state = itemStates.value[index];
  const newDate = new Date(state.currentWeekStart);
  newDate.setDate(newDate.getDate() + 7);
  state.currentWeekStart = newDate;
};

const getAvailableHoursForItem = (index) => {
  const state = itemStates.value[index];
  if (!state.selectedTurno || !state.selectedDate) return [];
  
  const hours = [];
  let startHour, endHour;
  
  switch (state.selectedTurno) {
    case 'manana': startHour = 9; endHour = 12; break;
    case 'tarde': startHour = 14; endHour = 18; break;
    case 'noche': startHour = 18; endHour = 21; break;
  }
  
  const now = new Date();
  const [year, month, day] = state.selectedDate.date.split('-').map(Number);
  const selectedDateObj = new Date(year, month - 1, day);
  
  const isToday = 
    selectedDateObj.getFullYear() === now.getFullYear() &&
    selectedDateObj.getMonth() === now.getMonth() &&
    selectedDateObj.getDate() === now.getDate();
  
  for (let h = startHour; h < endHour; h++) {
    for (let m = 0; m < 60; m += 40) {
      const hStr = h.toString().padStart(2, '0');
      const mStr = m.toString().padStart(2, '0');
      const timeString = `${hStr}:${mStr}`;
      
      let isPast = false;
      if (isToday) {
        const hourDate = new Date(year, month - 1, day, h, m, 0, 0);
        isPast = hourDate <= now;
      }
      
      hours.push({
        time: timeString,
        disabled: isPast
      });
    }
  }
  return hours;
};

const addItem = () => {
  form.items.push({ type: 'producto', id: '', cantidad: 1, precio: 0, barbero_id: '', fecha: currentFecha, hora: currentHora });
  itemStates.value.push(createItemState());
};

const removeItem = (index) => {
  form.items.splice(index, 1);
  itemStates.value.splice(index, 1);
};

const resetItem = (index) => {
  form.items[index].id = '';
  form.items[index].precio = 0;
  form.items[index].barbero_id = '';
  // Reset schedule for this row
  const state = itemStates.value[index];
  state.selectedHora = null;
  state.barberosDisponibles = [];
};

const getFilteredServicios = (barberoId) => {
  if (!barberoId) return [];
  const barbero = props.barberos.find(b => b.id === barberoId);
  return barbero ? barbero.servicios : [];
};

const updateItemPrice = (index) => {
  const item = form.items[index];
  if (item.type === 'producto') {
    const prod = props.productos.find(p => p.id === item.id);
    item.precio = prod ? parseFloat(prod.precio_venta) : 0;
  } else {
    // If it's a service, we look it up in the selected barbero's services
    const services = getFilteredServicios(item.barbero_id);
    const serv = services.find(s => s.id === item.id);
    item.precio = serv ? parseFloat(serv.precio) : 0;
  }
};

const addPago = () => {
  form.pagos.push({ tipo_pago_id: props.tiposPago[0]?.id || '', monto: 0 });
};

const removePago = (index) => {
  form.pagos.splice(index, 1);
};

const subtotal = computed(() => {
  return form.items.reduce((acc, item) => acc + (item.cantidad * item.precio), 0);
});

const totalPagado = computed(() => {
  return form.pagos.reduce((acc, p) => acc + (p.monto || 0), 0);
});

const restante = computed(() => {
  return subtotal.value - totalPagado.value;
});

const submit = () => {
  form.monto_total = subtotal.value;
  // If we have services, we'll use the barbero from the first service as the main barbero for the sale
  const firstService = form.items.find(i => i.type === 'servicio' && i.barbero_id);
  if (firstService) {
    form.barbero_id = firstService.barbero_id;
  }
  form.post(route('ventas.store'));
};
</script>

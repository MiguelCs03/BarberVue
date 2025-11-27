
<template>
  <Head title="Nueva Cita" />

  <AppLayout>
    
    <!-- ...resto del formulario... -->
    <!-- Page Header -->
    <div class="mb-6">
      <Link
        :href="route('citas.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver
      </Link>

      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Reservar Nueva Cita
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Selecciona fecha, hora y servicio para tu cita
      </p>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto">
      <Card>
        <DateCarousel
          v-model="selectedDate"
          :current-week-start="currentWeekStart"
          @previous-week="previousWeek"
          @next-week="nextWeek"
        />

        <TurnoSelector v-model="selectedTurno" />

        <HoursCarousel
          v-if="selectedDate && selectedTurno"
          v-model="selectedHora"
          :available-hours="availableHours"
        />

        <BarberoSelector
          v-if="selectedDate && selectedHora"
          v-model="selectedBarbero"
          :barberos="barberosDisponibles"
          :loading="loadingBarberos"
        />

        <ServiciosList
          v-if="selectedDate && selectedHora && selectedBarbero"
          v-model="selectedServicios"
          :servicios="availableServicios"
        />

        <CitaResumen
          v-if="selectedServicios.length > 0"
          :total-duracion="totalDuracion"
          :total-precio="totalPrecio"
          :pago-inicial="pagoInicial"
          :can-confirm="canConfirm"
          @confirmar="confirmarCita"
        />
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>

import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import DateCarousel from './components/DateCarousel.vue';
import TurnoSelector from './components/TurnoSelector.vue';
import HoursCarousel from './components/HoursCarousel.vue';
import BarberoSelector from './components/BarberoSelector.vue';
import ServiciosList from './components/ServiciosList.vue';
import CitaResumen from './components/CitaResumen.vue';


const props = defineProps({
  porcentajeReserva: {
    type: Number,
    default: 0
  }
});

// Estado
const currentWeekStart = ref(new Date());
const selectedDate = ref(null);
const selectedTurno = ref(null);
const selectedHora = ref(null);
const selectedBarbero = ref(null);
const selectedServicios = ref([]);
const serviciosPorBarbero = ref({});
const loadingBarberos = ref(false);
const barberosDisponibles = ref([]);
const todosServicios = ref([]);

// Computed
const availableHours = computed(() => {
  if (!selectedTurno.value || !selectedDate.value) return [];
  
  const hours = [];
  let startHour, endHour;
  
  switch (selectedTurno.value) {
    case 'manana':
      startHour = 9;
      endHour = 12;
      break;
    case 'tarde':
      startHour = 14;
      endHour = 18;
      break;
    case 'noche':
      startHour = 18;
      endHour = 21;
      break;
  }
  
  // Obtener fecha y hora actual
  const now = new Date();
  
  // Crear fecha seleccionada correctamente (evitar problemas de zona horaria)
  const [year, month, day] = selectedDate.value.date.split('-').map(Number);
  const selectedDateObj = new Date(year, month - 1, day);
  
  // Verificar si es hoy comparando solo año, mes y día
  const isToday = 
    selectedDateObj.getFullYear() === now.getFullYear() &&
    selectedDateObj.getMonth() === now.getMonth() &&
    selectedDateObj.getDate() === now.getDate();
  
  for (let h = startHour; h < endHour; h++) {
    for (let m = 0; m < 60; m += 40) {
      const hour = h.toString().padStart(2, '0');
      const minute = m.toString().padStart(2, '0');
      const timeString = `${hour}:${minute}`;
      
      // Verificar si la hora ya pasó (solo si es hoy)
      let isPast = false;
      if (isToday) {
        // Crear fecha con la hora específica para comparar
        const hourDate = new Date(year, month - 1, day, h, m, 0, 0);
        isPast = hourDate <= now; // Usar <= en lugar de < para incluir el minuto actual
      }
      
      hours.push({
        time: timeString,
        disabled: isPast
      });
    }
  }
  
  return hours;
});

const availableServicios = computed(() => {
  // if (selectedBarbero.value === null) {
  //   // Si no hay barbero seleccionado, mostrar todos los servicios disponibles
  //   return todosServicios.value;
  // }
  if (!selectedBarbero.value) return [];
  // Si hay barbero seleccionado, mostrar solo sus servicios
  const barbero = barberosDisponibles.value.find(b => b.id === selectedBarbero.value);
  if (!barbero) return [];
  
  // Ya tenemos los servicios completos, no necesitamos hacer filter
  return barbero.servicios;
});

const totalDuracion = computed(() => {
  return selectedServicios.value.reduce((sum, s) => sum + s.duracion_estimada, 0);
});

const totalPrecio = computed(() => {
  return selectedServicios.value.reduce((sum, s) => sum + s.precio, 0);
});

const pagoInicial = computed(() => {
  if (totalPrecio.value === 0) return 0;
  return (totalPrecio.value * (props.porcentajeReserva / 100));
});


// const canConfirm = computed(() => {
//   return selectedDate.value && selectedHora.value && selectedServicios.value.length > 0;
// });
const canConfirm = computed(() => {
  // Agregamos && selectedBarbero.value
  return selectedDate.value && selectedHora.value && selectedBarbero.value && selectedServicios.value.length > 0;
});

// Methods
const previousWeek = () => {
  const newDate = new Date(currentWeekStart.value);
  newDate.setDate(newDate.getDate() - 7);
  currentWeekStart.value = newDate;
};

const nextWeek = () => {
  const newDate = new Date(currentWeekStart.value);
  newDate.setDate(newDate.getDate() + 7);
  currentWeekStart.value = newDate;
};

const cargarBarberosYServicios = async () => {
  if (!selectedDate.value || !selectedHora.value) return;
  
  loadingBarberos.value = true;
  
  try {
    const response = await axios.post('/api/citas/barberos-disponibles', {
      fecha: selectedDate.value.date,
      hora: selectedHora.value
    });
    
    // Transformar barberos para agregar avatar generado
    barberosDisponibles.value = response.data.barberos.map(barbero => ({
      ...barbero,
      // Generar avatar con la primera letra del nombre
      avatar: generateAvatar(barbero.nombre),
      // Los servicios ya vienen completos desde el backend
      disponible: true // Ya viene filtrado desde el backend
    }));
    
    // Guardar todos los servicios disponibles
    todosServicios.value = response.data.servicios;
    
    // Resetear selecciones
    selectedBarbero.value = null;
    selectedServicios.value = [];
    
  } catch (error) {
    console.error('Error al cargar barberos:', error);
    alert('Error al cargar los datos. Por favor intenta nuevamente.');
    barberosDisponibles.value = [];
    todosServicios.value = [];
  } finally {
    loadingBarberos.value = false;
  }
};

/**
 * Genera un avatar con la primera letra del nombre
 * usando un servicio de avatares o creando uno personalizado
 */
const generateAvatar = (nombre) => {
  // Opción 1: Usar UI Avatars (servicio externo)
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(nombre)}&background=random&size=150`;
};

const confirmarCita = () => {
  if (!canConfirm.value) return;
  
  const citaData = {
    fecha: selectedDate.value.date,
    hora: selectedHora.value,
    barbero_id: selectedBarbero.value,
    servicios: selectedServicios.value.map(s => s.id),
    monto_total: totalPrecio.value,
    duracion_total: totalDuracion.value,
    pago_inicial: pagoInicial.value
  };
  
  console.log('Datos de la cita:', citaData);
  
  router.post('/cita/store', citaData, {
    preserveScroll: true,
    onSuccess: () => {
      alert('¡Cita confirmada exitosamente!');
    },
    onError: (errors) => {
      if (errors && errors.error) {
        alert(errors.error);
      } else {
        alert('Error al confirmar la cita');
      }
    }
  });
};

// Watchers
watch(selectedDate, (newVal) => {
  if (!newVal) {
    selectedTurno.value = null;
    selectedHora.value = null;
    selectedBarbero.value = null;
    selectedServicios.value = [];
    barberosDisponibles.value = [];
  }
});

watch(selectedTurno, (newVal) => {
  if (!newVal) {
    selectedHora.value = null;
    selectedBarbero.value = null;
    selectedServicios.value = [];
    barberosDisponibles.value = [];
  }else {
    // Si cambia el turno, limpiar la hora seleccionada si está deshabilitada
    if (selectedHora.value) {
      const horaObj = availableHours.value.find(h => h.time === selectedHora.value);
      if (horaObj && horaObj.disabled) {
        selectedHora.value = null;
      }
    }
  }
});

watch(selectedHora, (newVal) => {
  if (newVal) {
    cargarBarberosYServicios();
  } else {
    selectedBarbero.value = null;
    selectedServicios.value = [];
    barberosDisponibles.value = [];
  }
});

watch(selectedBarbero, (newVal, oldVal) => {
  // Guardar selección del barbero anterior
  if (oldVal !== undefined) {
    const oldKey = oldVal === null ? 'todos' : oldVal;
    if (selectedServicios.value.length > 0) {
      serviciosPorBarbero.value[oldKey] = [...selectedServicios.value];
    }
  }
  
  // Restaurar selección del nuevo barbero
  const newKey = newVal === null ? 'todos' : newVal;
  selectedServicios.value = serviciosPorBarbero.value[newKey] || [];
});
// watch(selectedBarbero, () => {
//   // Si cambia el barbero, reseteamos los servicios seleccionados para evitar errores
//   selectedServicios.value = [];
// });
</script>
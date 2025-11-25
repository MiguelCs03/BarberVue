<template>
  <Head title="Nueva Cita" />

  <AppLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <Link
        :href="route('citas-cliente.index')"
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

        <TurnoSelector 
          v-model="selectedTurno"
          :disabled="!isDateAvailable"
          :selected-date="selectedDate"
        />

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
          v-if="selectedDate && selectedHora && barberosDisponibles.length > 0"
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
import { ref, computed, watch, onMounted } from 'vue'; // Agrega onMounted
import { Head, Link, router, usePage } from '@inertiajs/vue3'; // Agrega usePage

import axios from 'axios';
import Swal from 'sweetalert2';
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

// Constantes de zona horaria
const TIMEZONE = 'America/La_Paz';
const page = usePage();
// Helper: Obtener fecha/hora actual en Bolivia
const getNowBolivia = () => {
  return new Date(new Date().toLocaleString('en-US', { timeZone: TIMEZONE }));
};

// Helper: Obtener fecha específica en Bolivia (sin hora)
const getDateBolivia = (year, month, day) => {
  // Crear fecha en UTC y convertir a Bolivia
  const date = new Date(Date.UTC(year, month - 1, day, 12, 0, 0));
  return new Date(date.toLocaleString('en-US', { timeZone: TIMEZONE }));
};

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

// Computed - Verifica si la fecha seleccionada está disponible
const isDateAvailable = computed(() => {
  if (!selectedDate.value) return false;
  
  const nowBolivia = getNowBolivia();
  const [year, month, day] = selectedDate.value.date.split('-').map(Number);
  const selectedDateBolivia = getDateBolivia(year, month, day);
  
  // Resetear horas para comparar solo fechas
  const todayBolivia = new Date(nowBolivia.getFullYear(), nowBolivia.getMonth(), nowBolivia.getDate());
  const selectedDateOnly = new Date(selectedDateBolivia.getFullYear(), selectedDateBolivia.getMonth(), selectedDateBolivia.getDate());
  
  return selectedDateOnly >= todayBolivia;
});

// Computed - Verifica si un turno específico está disponible
const isTurnoAvailable = computed(() => {
  return (turno) => {
    if (!selectedDate.value) return false;
    
    const nowBolivia = getNowBolivia();
    const [year, month, day] = selectedDate.value.date.split('-').map(Number);
    const selectedDateBolivia = getDateBolivia(year, month, day);
    
    const todayBolivia = new Date(nowBolivia.getFullYear(), nowBolivia.getMonth(), nowBolivia.getDate());
    const selectedDateOnly = new Date(selectedDateBolivia.getFullYear(), selectedDateBolivia.getMonth(), selectedDateBolivia.getDate());
    
    // Si es fecha futura, todos los turnos disponibles
    if (selectedDateOnly > todayBolivia) return true;
    
    // Si no es hoy, no disponible
    if (selectedDateOnly < todayBolivia) return false;
    
    // Si es hoy, verificar la hora actual
    const currentHour = nowBolivia.getHours();
    
    switch (turno) {
      case 'manana':
        return currentHour < 12;
      case 'tarde':
        return currentHour < 18;
      case 'noche':
        return currentHour < 21;
      default:
        return false;
    }
  };
});

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
    default:
      return [];
  }
  
  const nowBolivia = getNowBolivia();
  const [year, month, day] = selectedDate.value.date.split('-').map(Number);
  const selectedDateBolivia = getDateBolivia(year, month, day);
  
  // Comparar solo fechas (sin horas)
  const todayBolivia = new Date(nowBolivia.getFullYear(), nowBolivia.getMonth(), nowBolivia.getDate());
  const selectedDateOnly = new Date(selectedDateBolivia.getFullYear(), selectedDateBolivia.getMonth(), selectedDateBolivia.getDate());
  
  // Si la fecha es anterior a hoy, no mostrar horarios
  if (selectedDateOnly < todayBolivia) {
    return [];
  }
  
  // Verificar si es exactamente hoy
  const isToday = selectedDateOnly.getTime() === todayBolivia.getTime();
  
  console.log('=== DEBUG INFO ===');
  console.log('Hora actual Bolivia:', nowBolivia.toLocaleString('es-BO'));
  console.log('Fecha seleccionada:', selectedDate.value.date);
  console.log('Es hoy:', isToday);
  
  // Generar horarios
  for (let h = startHour; h < endHour; h++) {
    for (let m = 0; m < 60; m += 40) {
      const hour = h.toString().padStart(2, '0');
      const minute = m.toString().padStart(2, '0');
      const timeString = `${hour}:${minute}`;
      
      let isPastTime = false;
      
      // Solo deshabilitar horas pasadas si es HOY
      if (isToday) {
        // Crear fecha/hora específica en Bolivia
        const hourDateTime = new Date(year, month - 1, day, h, m, 0, 0);
        // Agregar margen de 5 minutos
        const nowWithMargin = new Date(nowBolivia.getTime() + 5 * 60000);
        isPastTime = hourDateTime <= nowWithMargin;
        
        if (isPastTime) {
          console.log(`Hora ${timeString} deshabilitada (ya pasó)`);
        }
      }
      
      hours.push({
        time: timeString,
        disabled: isPastTime
      });
    }
  }
  
  console.log('Total horas generadas:', hours.length);
  console.log('Horas deshabilitadas:', hours.filter(h => h.disabled).length);
  console.log('==================');
  
  return hours;
});

const availableServicios = computed(() => {
  if (selectedBarbero.value === null) {
    return todosServicios.value;
  }
  
  const barbero = barberosDisponibles.value.find(b => b.id === selectedBarbero.value);
  if (!barbero) return [];
  
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

const canConfirm = computed(() => {
  return selectedDate.value && 
         selectedHora.value && 
         selectedServicios.value.length > 0 &&
         isDateAvailable.value;
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
    const response = await axios.post('/citas/barberos-disponibles', {
      fecha: selectedDate.value.date,
      hora: selectedHora.value
    });
    
    barberosDisponibles.value = response.data.barberos.map(barbero => ({
      ...barbero,
      avatar: generateAvatar(barbero.nombre),
      disponible: true
    }));
    
    todosServicios.value = response.data.servicios;
    
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

const generateAvatar = (nombre) => {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(nombre)}&background=random&size=150`;
};

const confirmarCita = () => {
  if (!canConfirm.value) return;
  
  if (!isDateAvailable.value) {
    alert('La fecha seleccionada ya no está disponible');
    return;
  }
  
  const citaData = {
    fecha: selectedDate.value.date,
    hora: selectedHora.value,
    barbero_id: selectedBarbero.value,
    servicios: selectedServicios.value.map(s => s.id),
    monto_total: totalPrecio.value,
    duracion_total: totalDuracion.value,
    pago_inicial: pagoInicial.value
  };
  
  router.post('/cita/store', citaData, {
    preserveScroll: true,
    // onSuccess: () => {
    // //  alert('¡Cita confirmada exitosamente!');
    // },
    onError: (errors) => {
      console.log(errors)
      if (errors && errors.error) {
        Swal.fire({
          title: 'Error',
          text: errors.error,
          icon: 'error',
          confirmButtonColor: '#EF4444',
          background: 'var(--bg-primary)',
          color: 'var(--text-primary)'
        });
      } else {
        Swal.fire({
          title: 'Error',
          text: 'Error al confirmar la cita',
          icon: 'error',
          confirmButtonColor: '#EF4444',
          background: 'var(--bg-primary)',
          color: 'var(--text-primary)'
        });
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
    return;
  }

  if (!isDateAvailable.value) {
    selectedTurno.value = null;
    selectedHora.value = null;
    selectedBarbero.value = null;
    selectedServicios.value = [];
    barberosDisponibles.value = [];
  }
  
  if (selectedTurno.value && !isTurnoAvailable.value(selectedTurno.value)) {
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
    return;
  }
  
  if (selectedHora.value) {
    const horaObj = availableHours.value.find(h => h.time === selectedHora.value);
    if (!horaObj || horaObj.disabled) {
      selectedHora.value = null;
      selectedBarbero.value = null;
      selectedServicios.value = [];
      barberosDisponibles.value = [];
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
  if (oldVal !== undefined) {
    const oldKey = oldVal === null ? 'todos' : oldVal;
    if (selectedServicios.value.length > 0) {
      serviciosPorBarbero.value[oldKey] = [...selectedServicios.value];
    }
  }
  
  const newKey = newVal === null ? 'todos' : newVal;
  selectedServicios.value = serviciosPorBarbero.value[newKey] || [];
});

onMounted(() => {
  console.log('Componente montado');
  console.log('Props iniciales:', page.props);
  console.log('Flash inicial:', page.props.flash);
  checkFlashMessages();
});
// Observar cambios (por si Inertia hace un partial reload o redirect back)
watch(() => page.props.flash, (newFlash, oldFlash) => {
  console.log('Flash cambió de:', oldFlash, 'a:', newFlash);
  if (newFlash && (newFlash.error || newFlash.success)) {
    checkFlashMessages();
  }
}, { deep: true, immediate: true });

const checkFlashMessages = () => {
  const flash = page.props.flash;
  
  
  
  // Mensaje de Error (Business Logic, ej: "Horario ocupado")
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
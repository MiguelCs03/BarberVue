<template>
    <Head title="Crear Cita - Admin" />
  
    <AppLayout>
      <!-- Page Header -->
      <div class="mb-6">
        <Link
          :href="route('citas-admin.index')"
          class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
          :style="{ color: 'var(--color-primary)' }"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Volver
        </Link>
  
        <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
          Crear Cita (Administrador)
        </h1>
        <p :style="{ color: 'var(--text-secondary)' }">
          Busca un cliente (opcional) y selecciona fecha, hora y servicio para la cita
        </p>
      </div>
  
      <!-- Main Content -->
      <div class="max-w-4xl mx-auto">
        <Card>
          <!-- Componente de Búsqueda de Cliente -->
          <ClienteSearchAdmin 
            v-model="selectedCliente"
            @cleared="handleClienteCleared"
          />
  
          <!-- Selección de Fecha -->
          <DateCarousel
            v-model="selectedDate"
            :current-week-start="currentWeekStart"
            @previous-week="previousWeek"
            @next-week="nextWeek"
          />
  
          <!-- Selección de Turno -->
          <TurnoSelector v-model="selectedTurno" />
  
          <!-- Selección de Hora -->
          <HoursCarousel
            v-if="selectedDate && selectedTurno"
            v-model="selectedHora"
            :available-hours="availableHours"
          />
  
          <!-- Selección de Barbero -->
          <BarberoSelector
            v-if="selectedDate && selectedHora"
            v-model="selectedBarbero"
            :barberos="barberosDisponibles"
            :loading="loadingBarberos"
          />
  
          <!-- Selección de Servicios -->
          <ServiciosList
            v-if="selectedDate && selectedHora && selectedBarbero"
            v-model="selectedServicios"
            :servicios="availableServicios"
          />
  
          <!-- Resumen de Cita -->
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
  import ClienteSearchAdmin from './components/ClienteSearchAdmin.vue';
  import DateCarousel from './components/DateCarousel.vue';
  import TurnoSelector from './components/TurnoSelector.vue';
  import HoursCarousel from './components/HoursCarousel.vue';
  import BarberoSelector from './components/BarberoSelector.vue';
  import ServiciosList from './components/ServiciosList.vue';
  import CitaResumen from './components/CitaResumen.vue';
  import Swal from 'sweetalert2';

  const props = defineProps({
    porcentajeReserva: {
      type: Number,
      default: 0
    }
  });
  
  // Estado - Cliente
  const selectedCliente = ref(null);
  
  // Estado - Cita
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
  
  // Computed - Horas Disponibles
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
    
    const now = new Date();
    const [year, month, day] = selectedDate.value.date.split('-').map(Number);
    const selectedDateObj = new Date(year, month - 1, day);
    
    const isToday = 
      selectedDateObj.getFullYear() === now.getFullYear() &&
      selectedDateObj.getMonth() === now.getMonth() &&
      selectedDateObj.getDate() === now.getDate();
    
    for (let h = startHour; h < endHour; h++) {
      for (let m = 0; m < 60; m += 40) {
        const hour = h.toString().padStart(2, '0');
        const minute = m.toString().padStart(2, '0');
        const timeString = `${hour}:${minute}`;
        
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
  });
  
  // Computed - Servicios Disponibles
  const availableServicios = computed(() => {
    if (!selectedBarbero.value) return [];
    const barbero = barberosDisponibles.value.find(b => b.id === selectedBarbero.value);
    if (!barbero) return [];
    return barbero.servicios;
  });
  
  // Computed - Totales
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
    return selectedDate.value && selectedHora.value && selectedBarbero.value && selectedServicios.value.length > 0;
  });
  
  // Methods - Cliente
  const handleClienteCleared = () => {
    selectedDate.value = null;
    selectedTurno.value = null;
    selectedHora.value = null;
    selectedBarbero.value = null;
    selectedServicios.value = [];
    serviciosPorBarbero.value = {};
    barberosDisponibles.value = [];
    todosServicios.value = [];
    // Recargar barberos si ya hay fecha y hora seleccionadas
    // if (selectedDate.value && selectedHora.value) {
    //   cargarBarberosYServicios();
    // }
  };
  
  // Methods - Fechas
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
  
  // Methods - Barberos y Servicios
  const cargarBarberosYServicios = async () => {
    if (!selectedDate.value || !selectedHora.value) return;
    
    loadingBarberos.value = true;
    
    try {
        ///citas/barberos-disponibles-admin
        //TODO ajustar ruta par recibir el baseURL
      //const response = await axios.post('/citas/barberos-disponibles-admin', {
      const response = await axios.post(route('barberos-disponibles-admin'), {
        cliente_id: selectedCliente.value ? selectedCliente.value.id : null,
        fecha: selectedDate.value.date,
        hora: selectedHora.value
      });
      
      barberosDisponibles.value = response.data.barberos.map(barbero => ({
        ...barbero,
        avatar: generateAvatar(barbero.name),
        disponible: true
      }));
      
      todosServicios.value = response.data.servicios || [];
      
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
  
  // Methods - Confirmar Cita
  const confirmarCita = () => {
    if (!canConfirm.value) return;
    
    const citaData = {
      cliente_id: selectedCliente.value ? selectedCliente.value.id : null,
      fecha: selectedDate.value.date,
      hora: selectedHora.value,
      barbero_id: selectedBarbero.value,
      servicios: selectedServicios.value.map(s => s.id),
      monto_total: totalPrecio.value,
      duracion_total: totalDuracion.value,
      pago_inicial: pagoInicial.value
    };
    
    console.log('Datos de la cita:', citaData);
    
    router.post(route('citas-admin-store'), citaData, {
      preserveScroll: true,
    //   onSuccess: () => {
    //     alert('¡Cita confirmada exitosamente!');
    //   },
      onError: (errors) => {
        console.log(errors);
        if (errors && errors.error) {
          alert(errors.error);
          Swal.fire({
          title: 'Error',
          text: errors.error || 'Error al confirmar la cita',
          icon: 'error',
          confirmButtonColor: '#EF4444',
          background: 'var(--bg-primary)',
          color: 'var(--text-primary)'
        });
          
        } else {
          //console.log(errors);
          //alert('Error al confirmar la cita');
          Swal.fire({
            title: 'Error',
            text: errors.error || 'Error al confirmar la cita',
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
    }
  });
  
  watch(selectedTurno, (newVal) => {
    if (!newVal) {
      selectedHora.value = null;
      selectedBarbero.value = null;
      selectedServicios.value = [];
      barberosDisponibles.value = [];
    } else {
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
    if (oldVal !== undefined) {
      const oldKey = oldVal === null ? 'todos' : oldVal;
      if (selectedServicios.value.length > 0) {
        serviciosPorBarbero.value[oldKey] = [...selectedServicios.value];
      }
    }
    
    const newKey = newVal === null ? 'todos' : newVal;
    selectedServicios.value = serviciosPorBarbero.value[newKey] || [];
  });
  </script>
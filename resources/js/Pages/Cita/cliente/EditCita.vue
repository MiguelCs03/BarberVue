<template>
    <Head title="Editar Cita" />
  
    <AppLayout>
      <!-- Page Header -->
      <div class="mb-6">
        <Link
          :href="route('citas-cliente.show', cita.id)"
          class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
          :style="{ color: 'var(--color-primary)' }"
        >
          <ArrowLeftIcon class="w-5 h-5" />
          Volver a la Cita
        </Link>
  
        <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
          Editar Cita #{{ cita.id }}
        </h1>
        <p :style="{ color: 'var(--text-secondary)' }">
          Modifica la fecha, hora o barbero de tu cita
        </p>
      </div>
  
      <!-- Main Content -->
      <div class="max-w-4xl mx-auto">
        <Card>
          
  
          <!-- Selector de Fecha -->
          <DateCarousel
            v-model="selectedDate"
            :current-week-start="currentWeekStart"
            @previous-week="previousWeek"
            @next-week="nextWeek"
          />
  
          <!-- Selector de Turno -->
          <TurnoSelector v-model="selectedTurno" />
  
          <!-- Selector de Hora -->
          <HoursCarousel
            v-if="selectedDate && selectedTurno"
            v-model="selectedHora"
            :available-hours="availableHours"
          />
  
          <!-- Selector de Barbero -->
          <BarberosSelectorEdit
            v-if="selectedDate && selectedHora"
            v-model="selectedBarbero"
            :barberos="barberosDisponibles"
            :loading="loadingBarberos"
          />

          <ServiciosReadOnly :servicios="cita.servicios" />

          <!-- Botones de Acción -->
          <div v-if="canUpdate" class="flex gap-3 justify-end mt-6">
            <Link
              :href="route('citas-cliente.show', cita.id)"
              class="px-6 py-3 rounded-lg font-semibold transition-all hover:opacity-75"
              :style="{ 
                backgroundColor: 'var(--bg-secondary)', 
                color: 'var(--text-primary)' 
              }"
            >
              Cancelar
            </Link>
            
            <button
              @click="actualizarCita"
              :disabled="!hasChanges"
              class="px-6 py-3 rounded-lg font-semibold transition-all"
              :class="hasChanges ? 'hover:opacity-90' : 'opacity-50 cursor-not-allowed'"
              :style="{ 
                backgroundColor: 'var(--color-primary)', 
                color: 'white' 
              }"
            >
              Guardar Cambios
            </button>
          </div>
        </Card>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  import { Head, Link, router } from '@inertiajs/vue3';
  import axios from 'axios';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import Card from '@/Components/Card.vue';
  import DateCarousel from './components/DateCarousel.vue';
  import TurnoSelector from './components/TurnoSelector.vue';
  import HoursCarousel from './components/HoursCarousel.vue';
  
  import ServiciosReadOnly from './components/ServiciosReadOnly.vue';
  import { ArrowLeftIcon, ScissorsIcon } from '@heroicons/vue/24/outline';
  import Swal from 'sweetalert2';
  import BarberosSerlectorEdit from './components/BarberosSerlectorEdit.vue';
import BarberosSelectorEdit from './components/BarberosSerlectorEdit.vue';
  
  const props = defineProps({
    cita: {
      type: Object,
      required: true
    }
  });
  
  // Estado
  const currentWeekStart = ref(new Date());
  const selectedDate = ref(null);
  const selectedTurno = ref(null);
  const selectedHora = ref(null);
  const selectedBarbero = ref(null);
  const loadingBarberos = ref(false);
  const barberosDisponibles = ref([]);
  
  // Valores originales para detectar cambios
  const originalValues = ref({
    fecha: props.cita.fecha,
    hora: props.cita.hora,
    barbero_id: props.cita.barbero?.id || null
  });
  
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
  
  const hasChanges = computed(() => {
    return (
      selectedDate.value?.date !== originalValues.value.fecha ||
      selectedHora.value !== originalValues.value.hora ||
      selectedBarbero.value !== originalValues.value.barbero_id
    );
  });
  
//   const canUpdate = computed(() => {
//     return selectedDate.value && selectedHora.value;
//   });
  // Edit.vue

    const canUpdate = computed(() => {
    // Agregamos && selectedBarbero.value para obligar la selección
    return selectedDate.value && selectedHora.value && selectedBarbero.value;
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
  
  const cargarBarberosDisponibles = async () => {
    if (!selectedDate.value || !selectedHora.value) return;
    
    loadingBarberos.value = true;
    //TODO ajustar ruta par recibir el baseURL
    try {
      //const response = await axios.post('/citas/barberos-disponibles-edicion-cliente', {
      const response = await axios.post(route('barberos-disponibles-edicion'), {
        fecha: selectedDate.value.date,
        hora: selectedHora.value,
        cita_id: props.cita.id
      });
      console.log(response.data);
      barberosDisponibles.value = response.data.barberos.map(barbero => ({
        ...barbero,
        avatar: generateAvatar(barbero.nombre),
        disponible: true
      }));
      
      // Si el barbero actual no está en la lista, agregarlo
      //quizas no sea necesario dado que el backend retorna el barbero seleccionado
    //   if (props.cita.barbero && !barberosDisponibles.value.find(b => b.id === props.cita.barbero.id)) {
    //     barberosDisponibles.value.unshift({
    //       id: props.cita.barbero.id,
    //       nombre: props.cita.barbero.nombre,
    //       avatar: generateAvatar(props.cita.barbero.nombre),
    //       disponible: true
    //     });
    //   }
      
      // Preseleccionar el barbero actual si está disponible
      if (
        props.cita.barbero && 
        barberosDisponibles.value.find(b => b.id === props.cita.barbero.id)
      ) {
        selectedBarbero.value = props.cita.barbero.id;
      } else {
        selectedBarbero.value = null;
      }
      
    } catch (error) {
      console.error('Error al cargar barberos:', error);
      Swal.fire({
        title: 'Error',
        text: 'No se pudieron cargar los barberos disponibles',
        icon: 'error',
        confirmButtonColor: '#EF4444',
        background: 'var(--bg-primary)',
        color: 'var(--text-primary)'
      });
      barberosDisponibles.value = [];
    } finally {
      loadingBarberos.value = false;
    }
  };
  
  const generateAvatar = (nombre) => {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(nombre)}&background=random&size=150`;
  };
  
  const getTurnoFromHora = (hora) => {
    const [hours] = hora.split(':').map(Number);
    
    if (hours >= 9 && hours < 14) return 'manana';
    if (hours >= 14 && hours < 18) return 'tarde';
    if (hours >= 18 && hours < 21) return 'noche';
    
    return null;
  };
  
  const actualizarCita = () => {
    if (!hasChanges.value) return;
    
    Swal.fire({
      title: '¿Confirmar cambios?',
      text: 'Se actualizará la fecha, hora o barbero de tu cita',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: 'var(--color-primary)',
      cancelButtonColor: '#6B7280',
      confirmButtonText: 'Sí, actualizar',
      cancelButtonText: 'Cancelar',
      background: 'var(--bg-primary)',
      color: 'var(--text-primary)'
    }).then((result) => {
      if (result.isConfirmed) {
        router.put(route('citas-cliente.update', props.cita.id), {
          fecha: selectedDate.value.date,
          hora: selectedHora.value,
          barbero_id: selectedBarbero.value
        });
      }
    });
  };
  
  // Inicializar valores
  onMounted(() => {
    // Preseleccionar fecha
    const [year, month, day] = props.cita.fecha.split('-').map(Number);
    const citaDate = new Date(year, month - 1, day);
    
    selectedDate.value = {
      date: props.cita.fecha,
      day: citaDate.getDate(),
      dayName: citaDate.toLocaleDateString('es-ES', { weekday: 'short' }),
      fullDate: citaDate
    };
    
    // Preseleccionar turno y hora
    selectedTurno.value = getTurnoFromHora(props.cita.hora);
    selectedHora.value = props.cita.hora;
    
    // Ajustar la semana actual para que contenga la fecha de la cita
    currentWeekStart.value = new Date(citaDate);
    currentWeekStart.value.setDate(citaDate.getDate() - citaDate.getDay());
  });
  
  // Watchers
  watch(selectedDate, (newVal) => {
    if (!newVal) {
      selectedTurno.value = null;
      selectedHora.value = null;
      selectedBarbero.value = null;
      barberosDisponibles.value = [];
    }
  });
  
  watch(selectedTurno, (newVal) => {
    if (!newVal) {
      selectedHora.value = null;
      selectedBarbero.value = null;
      barberosDisponibles.value = [];
    }
  });
  
  watch(selectedHora, (newVal) => {
    if (newVal) {
      cargarBarberosDisponibles();
    } else {
      selectedBarbero.value = null;
      barberosDisponibles.value = [];
    }
  });
  </script>
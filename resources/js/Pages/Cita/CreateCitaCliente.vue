<template>
    <Head title="Nueva Cita" />
  
    <AppLayout>
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
          <!-- Selector de Mes -->
          <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">
                {{ currentMonthYear }}
              </h2>
              <div class="flex gap-2">
                <button
                  @click="previousWeek"
                  class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                  :style="{ color: 'var(--text-primary)' }"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button
                  @click="nextWeek"
                  class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                  :style="{ color: 'var(--text-primary)' }"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>
            </div>
  
            <!-- Carrusel de Días -->
             <div class="flex justify-center">
                 <div class="flex gap-3 overflow-x-auto pb-2 hide-scrollbar">
                   <button
                     v-for="day in visibleDays"
                     :key="day.date"
                     @click="selectDate(day)"
                     :disabled="!day.available"
                     :class="[
                       'flex-shrink-0 w-20 py-3 px-2 rounded-lg text-center transition-all',
                       selectedDate?.date === day.date
                         ? 'btn-primary'
                         : day.available
                         ? 'border-2 hover:border-opacity-50'
                         : 'opacity-40 cursor-not-allowed'
                     ]"
                     :style="day.available && selectedDate?.date !== day.date ? { 
                       borderColor: 'var(--color-primary)', 
                       color: 'var(--text-primary)' 
                     } : {}"
                   >
                     <div class="text-xs font-medium mb-1">
                       {{ day.dayName }}
                     </div>
                     <div class="text-2xl font-bold">
                       {{ day.dayNumber }}
                     </div>
                     <div v-if="day.available" class="w-2 h-2 rounded-full mx-auto mt-2" 
                          :style="{ backgroundColor: selectedDate?.date === day.date ? 'white' : 'var(--color-primary)' }">
                     </div>
                   </button>
                 </div>
             </div>

          </div>
  
          <!-- Selector de Turno -->
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
              Turno
            </h3>
            <div class="flex gap-2">
              <button
                v-for="turno in turnos"
                :key="turno.value"
                @click="selectedTurno = turno.value"
                :class="[
                  'flex-1 py-2 px-4 rounded-lg transition-all',
                  selectedTurno === turno.value ? 'btn-primary' : 'btn-secondary'
                ]"
              >
                {{ turno.label }}
              </button>
            </div>
          </div>
  
          <!-- Carrusel de Horas -->
          <div v-if="selectedDate" class="mb-6">
            <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
              Horario Disponible
            </h3>
            <div class="flex justify-center">
              <div ref="hoursContainer" class="flex gap-2 overflow-x-auto pb-2 hide-scrollbar scroll-smooth snap-x snap-mandatory">
                <div class="flex-shrink-0 w-8"></div>
                <button
                  v-for="hora in availableHours"
                  :key="hora"
                  @click="selectedHora = hora"
                  :class="[
                    'flex-shrink-0 py-2 px-4 rounded-lg transition-all font-medium snap-center',
                    selectedHora === hora ? 'btn-primary' : 'btn-secondary'
                  ]"
                >
                  {{ hora }}
                </button>
                <div class="flex-shrink-0 w-8"></div>
              </div>
            </div>
          </div>
  
          <!-- Selector de Barbero -->
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
              Selecciona un Barbero (Opcional)
            </h3>
            <p class="text-sm mb-4" :style="{ color: 'var(--text-secondary)' }">
              Si no seleccionas un barbero, se mostrarán todos los servicios disponibles
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
              <button
                @click="cambiarBarbero(null)"
                :class="[
                  'p-4 rounded-lg border-2 transition-all text-left',
                  selectedBarbero === null ? 'border-opacity-100' : 'border-opacity-20 hover:border-opacity-40'
                ]"
                :style="{ 
                  borderColor: 'var(--color-primary)',
                  backgroundColor: selectedBarbero === null ? 'var(--color-primary)' : 'transparent',
                  color: selectedBarbero === null ? 'white' : 'var(--text-primary)'
                }"
              >
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-full flex items-center justify-center" 
                       :style="{ backgroundColor: selectedBarbero === null ? 'rgba(255,255,255,0.2)' : 'var(--color-primary)', opacity: selectedBarbero === null ? 1 : 0.2 }">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-semibold">Cualquier barbero</p>
                    <p class="text-xs opacity-75">Todos los servicios</p>
                  </div>
                </div>
              </button>
  
              <button
                v-for="barbero in barberos"
                :key="barbero.id"
                @click="cambiarBarbero(barbero.id)"
                :class="[
                  'p-4 rounded-lg border-2 transition-all text-left',
                  selectedBarbero === barbero.id ? 'border-opacity-100' : 'border-opacity-20 hover:border-opacity-40'
                ]"
                :style="{ 
                  borderColor: 'var(--color-primary)',
                  backgroundColor: selectedBarbero === barbero.id ? 'var(--color-primary)' : 'transparent',
                  color: selectedBarbero === barbero.id ? 'white' : 'var(--text-primary)'
                }"
              >
                <div class="flex items-center gap-3">
                  <img 
                    :src="barbero.avatar" 
                    :alt="barbero.nombre"
                    class="w-12 h-12 rounded-full object-cover"
                  />
                  <div>
                    <p class="font-semibold">{{ barbero.nombre }}</p>
                    <p class="text-xs opacity-75">{{ barbero.servicios.length }} servicios</p>
                  </div>
                </div>
              </button>
            </div>
          </div>
  
          <!-- Servicios Disponibles -->
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
              Servicios Disponibles
            </h3>
            <div class="space-y-2">
              <button
                v-for="servicio in availableServicios"
                :key="servicio.id"
                @click="toggleServicio(servicio)"
                :class="[
                  'w-full p-4 rounded-lg border-2 transition-all text-left',
                  isServicioSelected(servicio.id) ? 'border-opacity-100' : 'border-opacity-20 hover:border-opacity-40'
                ]"
                :style="{ 
                  borderColor: 'var(--color-primary)',
                  backgroundColor: isServicioSelected(servicio.id) ? 'var(--color-primary)' : 'transparent',
                  color: isServicioSelected(servicio.id) ? 'white' : 'var(--text-primary)'
                }"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <p class="font-semibold mb-1">{{ servicio.nombre }}</p>
                    <p class="text-sm opacity-75">{{ servicio.descripcion }}</p>
                    <div class="flex items-center gap-4 mt-2 text-sm">
                      <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ servicio.duracion }} min
                      </span>
                      <span class="font-bold">Bs. {{ servicio.precio.toFixed(2) }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div :class="[
                      'w-6 h-6 rounded border-2 flex items-center justify-center transition-all',
                      isServicioSelected(servicio.id) ? 'border-white' : 'border-current'
                    ]">
                      <svg v-if="isServicioSelected(servicio.id)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                  </div>
                </div>
              </button>
            </div>
          </div>
  
          <!-- Resumen y Total -->
          <div v-if="selectedServicios.length > 0" class="border-t pt-6" :style="{ borderColor: 'var(--text-secondary)' }">
            <div class="flex items-center justify-between mb-4">
              <span class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                Duración Total:
              </span>
              <span class="text-xl font-bold" :style="{ color: 'var(--color-secondary)' }">
                {{ totalDuracion }} min
              </span>
            </div>
            <div class="flex items-center justify-between mb-6">
              <span class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                Total:
              </span>
              <span class="text-2xl font-bold" :style="{ color: 'var(--color-primary)' }">
                Bs. {{ totalPrecio.toFixed(2) }}
              </span>
            </div>
  
            <button
              @click="confirmarCita"
              :disabled="!canConfirm"
              :class="[
                'w-full py-3 rounded-lg font-semibold text-lg transition-all',
                canConfirm ? 'btn-primary' : 'opacity-50 cursor-not-allowed btn-secondary'
              ]"
            >
              Confirmar Cita
            </button>
          </div>
        </Card>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref, computed, onMounted, nextTick } from 'vue';
  import { Head, Link, router } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import Card from '@/Components/Card.vue';
  
  // Mock Data
  const barberos = ref([
    {
      id: 1,
      nombre: 'Carlos Méndez',
      avatar: 'https://i.pravatar.cc/150?img=12',
      servicios: [1, 2, 3, 5]
    },
    {
      id: 2,
      nombre: 'Juan Pérez',
      avatar: 'https://i.pravatar.cc/150?img=13',
      servicios: [1, 2, 4, 6]
    },
    {
      id: 3,
      nombre: 'Miguel Torres',
      avatar: 'https://i.pravatar.cc/150?img=14',
      servicios: [2, 3, 4, 5, 6]
    },
  ]);
  
  const todosServicios = ref([
    { id: 1, nombre: 'Corte Clásico', descripcion: 'Corte tradicional con tijera', duracion: 30, precio: 50 },
    { id: 2, nombre: 'Corte + Barba', descripcion: 'Corte completo y arreglo de barba', duracion: 45, precio: 75 },
    { id: 3, nombre: 'Fade Degradado', descripcion: 'Corte moderno con degradado', duracion: 40, precio: 65 },
    { id: 4, nombre: 'Barba Completa', descripcion: 'Arreglo y diseño de barba', duracion: 25, precio: 40 },
    { id: 5, nombre: 'Corte Niño', descripcion: 'Corte especial para niños', duracion: 20, precio: 35 },
    { id: 6, nombre: 'Afeitado Clásico', descripcion: 'Afeitado con navaja y toalla caliente', duracion: 30, precio: 45 },
  ]);
  
  // Estado
  const currentWeekStart = ref(new Date());
  const selectedDate = ref(null);
  const selectedTurno = ref('manana');
  const selectedHora = ref(null);
  const selectedBarbero = ref(null);
  const selectedServicios = ref([]);
  const serviciosPorBarbero = ref({}); // Guardamos las selecciones por barbero
  const hoursContainer = ref(null);
  const daysContainer = ref(null);
  
  const turnos = [
    { value: 'manana', label: 'Mañana' },
    { value: 'tarde', label: 'Tarde' },
    { value: 'noche', label: 'Noche' },
  ];
  
  // Computed
  const currentMonthYear = computed(() => {
    const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return `${months[currentWeekStart.value.getMonth()]} ${currentWeekStart.value.getFullYear()}`;
  });
  
  const visibleDays = computed(() => {
    const days = [];
    const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    
    for (let i = 0; i < 7; i++) {
      const date = new Date(currentWeekStart.value);
      date.setDate(date.getDate() + i);
      
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const available = date >= today;
      
      days.push({
        date: date.toISOString().split('T')[0],
        dayName: dayNames[date.getDay()],
        dayNumber: date.getDate(),
        fullDate: date,
        available
      });
    }
    return days;
  });
  
  const availableHours = computed(() => {
    if (!selectedTurno.value) return [];
    
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
    
    for (let h = startHour; h < endHour; h++) {
      for (let m = 0; m < 60; m += 40) {
        const hour = h.toString().padStart(2, '0');
        const minute = m.toString().padStart(2, '0');
        hours.push(`${hour}:${minute}`);
      }
    }
    
    return hours;
  });
  
  const availableServicios = computed(() => {
    if (selectedBarbero.value === null) {
      return todosServicios.value;
    }
    
    const barbero = barberos.value.find(b => b.id === selectedBarbero.value);
    if (!barbero) return [];
    
    return todosServicios.value.filter(s => barbero.servicios.includes(s.id));
  });
  
  const totalDuracion = computed(() => {
    return selectedServicios.value.reduce((sum, s) => sum + s.duracion, 0);
  });
  
  const totalPrecio = computed(() => {
    return selectedServicios.value.reduce((sum, s) => sum + s.precio, 0);
  });
  
  const canConfirm = computed(() => {
    return selectedDate.value && selectedHora.value && selectedServicios.value.length > 0;
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
  
  const selectDate = (day) => {
    if (!day.available) return;
    selectedDate.value = day;
    selectedHora.value = null;
    
    // Centrar el día seleccionado
    nextTick(() => {
      if (daysContainer.value) {
        const dayButtons = daysContainer.value.querySelectorAll('button');
        const selectedIndex = visibleDays.value.findIndex(d => d.date === day.date);
        if (dayButtons[selectedIndex]) {
          dayButtons[selectedIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
      }
    });
  };
  
  const toggleServicio = (servicio) => {
    const index = selectedServicios.value.findIndex(s => s.id === servicio.id);
    if (index > -1) {
      selectedServicios.value.splice(index, 1);
    } else {
      selectedServicios.value.push(servicio);
    }
    
    // Guardar selecciones por barbero
    const barberoKey = selectedBarbero.value === null ? 'todos' : selectedBarbero.value;
    serviciosPorBarbero.value[barberoKey] = [...selectedServicios.value];
  };
  
  const isServicioSelected = (servicioId) => {
    return selectedServicios.value.some(s => s.id === servicioId);
  };
  
  const cambiarBarbero = (barberoId) => {
    // Guardar selección actual antes de cambiar
    const barberoActualKey = selectedBarbero.value === null ? 'todos' : selectedBarbero.value;
    if (selectedServicios.value.length > 0) {
      serviciosPorBarbero.value[barberoActualKey] = [...selectedServicios.value];
    }
    
    // Cambiar barbero
    selectedBarbero.value = barberoId;
    
    // Restaurar selecciones del nuevo barbero (o vaciar si no hay)
    const nuevoBarberoKey = barberoId === null ? 'todos' : barberoId;
    selectedServicios.value = serviciosPorBarbero.value[nuevoBarberoKey] || [];
  };
  
  const checkHoursScroll = () => {
    // Centrar la primera hora al cargar
    if (hoursContainer.value && availableHours.value.length > 0) {
      const firstButton = hoursContainer.value.querySelector('button');
      if (firstButton) {
        firstButton.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      }
    }
  };
  
  const confirmarCita = () => {
    if (!canConfirm.value) return;
    
    const citaData = {
      fecha: selectedDate.value.date,
      hora: selectedHora.value,
      barbero_id: selectedBarbero.value,
      servicios: selectedServicios.value.map(s => s.id),
      total: totalPrecio.value,
      duracion_total: totalDuracion.value
    };
    
    console.log('Datos de la cita:', citaData);
    
    // Aquí harías el POST con Inertia
    // router.post(route('citas.store'), citaData);
    
    alert('Cita confirmada! (Mock - revisa consola)');
  };
  
  // Lifecycle
  onMounted(() => {
    nextTick(() => {
      // Centrar el primer día disponible
      if (daysContainer.value) {
        const firstAvailableDay = visibleDays.value.find(d => d.available);
        if (firstAvailableDay) {
          const dayButtons = daysContainer.value.querySelectorAll('button');
          const index = visibleDays.value.findIndex(d => d.date === firstAvailableDay.date);
          if (dayButtons[index]) {
            dayButtons[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
          }
        }
      }
      
      // Centrar primera hora cuando se carga
      if (hoursContainer.value) {
        checkHoursScroll();
      }
    });
  });
  </script>
  
  <style scoped>
  .hide-scrollbar {
    scrollbar-width: none;
    -ms-overflow-style: none;
  }
  
  .hide-scrollbar::-webkit-scrollbar {
    display: none;
  }
  
  /* Snap scroll para centrar elementos */
  .snap-x {
    scroll-snap-type: x mandatory;
  }
  
  .snap-center {
    scroll-snap-align: center;
  }
  </style>
<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Dashboard
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Bienvenido, {{ $page.props.auth.user.name }}
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <!-- Total Users -->
      <Card v-if="$page.props.auth.user.rol === 'barbero'">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Total Usuarios
            </p>
            <p class="text-3xl font-bold mt-2" :style="{ color: 'var(--text-primary)' }">
              8
            </p>
          </div>
          <div class="p-3 rounded-full" :style="{ backgroundColor: 'var(--bg-secondary)' }">
            <svg class="w-8 h-8" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </Card>

      <!-- Total Products -->
      <Card v-if="$page.props.auth.user.rol === 'barbero'">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Productos
            </p>
            <p class="text-3xl font-bold mt-2" :style="{ color: 'var(--text-primary)' }">
              8
            </p>
          </div>
          <div class="p-3 rounded-full" :style="{ backgroundColor: 'var(--bg-secondary)' }">
            <svg class="w-8 h-8" :style="{ color: 'var(--color-secondary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
      </Card>

      <!-- Total Services -->
      <Card v-if="$page.props.auth.user.rol === 'barbero'">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Servicios
            </p>
            <p class="text-3xl font-bold mt-2" :style="{ color: 'var(--text-primary)' }">
              8
            </p>
          </div>
          <div class="p-3 rounded-full" :style="{ backgroundColor: 'var(--bg-secondary)' }">
            <svg class="w-8 h-8" :style="{ color: 'var(--color-accent)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </Card>

      <!-- Total Appointments -->
      <Card>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
              Citas {{ $page.props.auth.user.rol === 'barbero' ? 'Totales' : 'Mis Citas' }}
            </p>
            <p class="text-3xl font-bold mt-2" :style="{ color: 'var(--text-primary)' }">
              {{ $page.props.auth.user.rol === 'barbero' ? '6' : '2' }}
            </p>
          </div>
          <div class="p-3 rounded-full" :style="{ backgroundColor: 'var(--bg-secondary)' }">
            <svg class="w-8 h-8" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </Card>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Recent Appointments -->
      <Card>
        <h2 class="text-xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
          Próximas Citas
        </h2>
        <div class="space-y-3">
          <div 
            v-for="appointment in recentAppointments" 
            :key="appointment.id"
            class="flex items-center justify-between p-3 rounded-lg transition-all hover:shadow-md cursor-pointer"
            :style="{ backgroundColor: 'var(--bg-secondary)' }"
            @click="$inertia.visit(route('appointments.show', appointment.id))"
          >
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-full" :style="{ backgroundColor: 'var(--bg-primary)' }">
                <svg class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div>
                <p class="font-medium" :style="{ color: 'var(--text-primary)' }">
                  {{ appointment.cliente }}
                </p>
                <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
                  {{ appointment.servicio }} - {{ appointment.fecha }}
                </p>
              </div>
            </div>
            <Badge :variant="appointment.estado === 'confirmada' ? 'success' : 'warning'">
              {{ appointment.estado }}
            </Badge>
          </div>
        </div>
        <Link :href="route('appointments.index')" class="block mt-4 text-center btn-secondary">
          Ver todas las citas
        </Link>
      </Card>

      <!-- Quick Links -->
      <Card>
        <h2 class="text-xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
          Accesos Rápidos
        </h2>
        <div class="grid grid-cols-2 gap-3">
          <Link
            :href="route('appointments.create')"
            class="flex flex-col items-center gap-2 p-4 rounded-lg transition-all hover:shadow-md"
            :style="{ backgroundColor: 'var(--bg-secondary)' }"
          >
            <svg class="w-8 h-8" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="text-sm font-medium text-center" :style="{ color: 'var(--text-primary)' }">
              Nueva Citas
            </span>
          </Link>

          <Link
            v-if="$page.props.auth.user.rol === 'barbero'"
            :href="route('products.index')"
            class="flex flex-col items-center gap-2 p-4 rounded-lg transition-all hover:shadow-md"
            :style="{ backgroundColor: 'var(--bg-secondary)' }"
          >
            <svg class="w-8 h-8" :style="{ color: 'var(--color-secondary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span class="text-sm font-medium text-center" :style="{ color: 'var(--text-primary)' }">
              Productos
            </span>
          </Link>

          <Link
            v-if="$page.props.auth.user.rol === 'barbero'"
            :href="route('services.index')"
            class="flex flex-col items-center gap-2 p-4 rounded-lg transition-all hover:shadow-md"
            :style="{ backgroundColor: 'var(--bg-secondary)' }"
          >
            <svg class="w-8 h-8" :style="{ color: 'var(--color-accent)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-center" :style="{ color: 'var(--text-primary)' }">
              Servicios
            </span>
          </Link>

          <Link
            v-if="$page.props.auth.user.rol === 'barbero'"
            :href="route('users.index')"
            class="flex flex-col items-center gap-2 p-4 rounded-lg transition-all hover:shadow-md"
            :style="{ backgroundColor: 'var(--bg-secondary)' }"
          >
            <svg class="w-8 h-8" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="text-sm font-medium text-center" :style="{ color: 'var(--text-primary)' }">
              Usuarios
            </span>
          </Link>
        </div>
      </Card>
    </div>

    <!-- Welcome Message for Clientes -->
    <Card v-if="$page.props.auth.user.rol === 'cliente'" class="text-center py-8">
      <div class="max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
          ¡Bienvenido a BarberShop! 💈
        </h2>
        <p class="mb-6" :style="{ color: 'var(--text-secondary)' }">
          Gestiona tus citas de manera fácil y rápida. Agenda tu próxima visita con nosotros.
        </p>
        <Link :href="route('appointments.create')" class="btn-primary inline-block">
          Agendar Nueva Cita
        </Link>
      </div>
    </Card>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';

// Mock data for recent appointments
const recentAppointments = [
  { id: 2, cliente: 'Sofía Reyes', servicio: 'Corte + Barba', fecha: '11/11/2025 14:00', estado: 'confirmada' },
  { id: 4, cliente: 'Marcos Vargas', servicio: 'Corte de cabello', fecha: '12/11/2025 09:30', estado: 'pendiente' },
  { id: 5, cliente: 'Valeria Ramos', servicio: 'Tratamiento capilar', fecha: '12/11/2025 11:00', estado: 'confirmada' },
];
</script>

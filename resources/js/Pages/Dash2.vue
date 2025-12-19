<template>
  <Head title="Dashboard" />

  <AppLayout>
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-black tracking-tighter uppercase italic" :style="{ color: 'var(--text-primary)' }">
        Dashboard 
      </h1>
      <p class="text-sm font-bold mt-2 uppercase tracking-[0.3em] opacity-70" :style="{ color: 'var(--text-secondary)' }">
        Bienvenido de nuevo, {{ $page.props.auth.user.name }}
      </p>
    </div>

    <!-- Stats Cards: ADMIN / BARBERO -->
    <div v-if="isAdminOrBarbero" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Usuarios -->
      <Card class="hover:scale-[1.02] transition-transform duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] mb-1" :style="{ color: 'var(--text-secondary)' }">Comunidad</p>
            <p class="text-3xl font-black" :style="{ color: 'var(--text-primary)' }">{{ metrics.usuarios.total }}</p>
            <div class="flex gap-2 mt-2 text-[9px] font-bold uppercase tracking-wider">
              <span class="px-2 py-0.5 rounded-full" :style="{ backgroundColor: 'rgba(99, 102, 241, 0.1)', color: 'var(--color-primary)' }">Clientes: {{ metrics.usuarios.clientes }}</span>
              <span class="px-2 py-0.5 rounded-full" :style="{ backgroundColor: 'rgba(16, 185, 129, 0.1)', color: '#10b981' }">Barberos: {{ metrics.usuarios.barberos }}</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl shadow-inner" :style="{ backgroundColor: 'var(--bg-secondary)', color: 'var(--color-primary)' }">
            <UserGroupIcon class="w-8 h-8" />
          </div>
        </div>
      </Card>

      <!-- Productos -->
      <Card class="hover:scale-[1.02] transition-transform duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] mb-1" :style="{ color: 'var(--text-secondary)' }">Productos</p>
            <p class="text-3xl font-black" :style="{ color: 'var(--text-primary)' }">{{ metrics.productos.total }}</p>
            <div class="flex gap-2 mt-2 text-[9px] font-bold uppercase tracking-wider">
              <span class="px-2 py-0.5 rounded-full" :style="{ backgroundColor: 'rgba(16, 185, 129, 0.1)', color: '#10b981' }">Activos: {{ metrics.productos.activos }}</span>
              <span class="px-2 py-0.5 rounded-full" :style="{ backgroundColor: 'rgba(244, 63, 94, 0.1)', color: '#f43f5e' }">Inactivos: {{ metrics.productos.inactivos }}</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl shadow-inner" :style="{ backgroundColor: 'var(--bg-secondary)', color: '#10b981' }">
            <ShoppingBagIcon class="w-8 h-8" />
          </div>
        </div>
      </Card>

      <!-- Servicios -->
      <Card class="hover:scale-[1.02] transition-transform duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] mb-1" :style="{ color: 'var(--text-secondary)' }">Servicios</p>
            <p class="text-3xl font-black" :style="{ color: 'var(--text-primary)' }">{{ metrics.servicios.total }}</p>
            <div class="flex gap-2 mt-2 text-[9px] font-bold uppercase tracking-wider">
              <span class="px-2 py-0.5 rounded-full" :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)', color: 'var(--color-accent)' }">Activos: {{ metrics.servicios.activos }}</span>
              <span class="px-2 py-0.5 rounded-full" :style="{ backgroundColor: 'rgba(244, 63, 94, 0.1)', color: '#f43f5e' }">Inactivos: {{ metrics.servicios.inactivos }}</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl shadow-inner" :style="{ backgroundColor: 'var(--bg-secondary)', color: 'var(--color-accent)' }">
            <SparklesIcon class="w-8 h-8" />
          </div>
        </div>
      </Card>

      <!-- Citas Totales -->
      <Card class="hover:scale-[1.02] transition-transform duration-300" :style="{ borderLeft: '4px solid var(--color-primary)' }">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] mb-1" :style="{ color: 'var(--color-primary)' }">Citas</p>
            <p class="text-3xl font-black" :style="{ color: 'var(--text-primary)' }">{{ metrics.citas.total }}</p>
            <div class="flex gap-2 mt-2 text-[9px] font-bold uppercase tracking-wider">
              <span class="px-2 py-0.5 rounded-full" :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)', color: '#3b82f6' }">Pdt: {{ metrics.citas.pendientes }}</span>
              <span class="px-2 py-0.5 rounded-full" :style="{ backgroundColor: 'rgba(16, 185, 129, 0.1)', color: '#10b981' }">Conf: {{ metrics.citas.confirmadas }}</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl text-white shadow-lg" :style="{ backgroundColor: 'var(--color-primary)', boxShadow: '0 10px 15px -3px rgba(99, 102, 241, 0.2)' }">
            <CalendarDaysIcon class="w-8 h-8" />
          </div>
        </div>
      </Card>
    </div>

    <!-- Stats Cards: CLIENTE -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <Card :style="{ borderBottom: '4px solid #3b82f6' }">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-black uppercase tracking-widest mb-1" :style="{ color: '#3b82f6' }">Citas Pendientes</p>
            <p class="text-4xl font-black" :style="{ color: 'var(--text-primary)' }">{{ metrics.citas.pendientes }}</p>
          </div>
          <div class="p-4 rounded-2xl" :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)', color: '#3b82f6' }"><ClockIcon class="w-10 h-10" /></div>
        </div>
      </Card>

      <Card :style="{ borderBottom: '4px solid #10b981' }">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-black uppercase tracking-widest mb-1" :style="{ color: '#10b981' }">Citas Realizadas</p>
            <p class="text-4xl font-black" :style="{ color: 'var(--text-primary)' }">{{ metrics.citas.confirmadas }}</p>
          </div>
          <div class="p-4 rounded-2xl" :style="{ backgroundColor: 'rgba(16, 185, 129, 0.1)', color: '#10b981' }"><CheckCircleIcon class="w-10 h-10" /></div>
        </div>
      </Card>

      <Card :style="{ borderBottom: '4px solid #f43f5e' }">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-black uppercase tracking-widest mb-1" :style="{ color: '#f43f5e' }">Canceladas</p>
            <p class="text-4xl font-black" :style="{ color: 'var(--text-primary)' }">{{ metrics.citas.canceladas }}</p>
          </div>
          <div class="p-4 rounded-2xl" :style="{ backgroundColor: 'rgba(244, 63, 94, 0.1)', color: '#f43f5e' }"><XCircleIcon class="w-10 h-10" /></div>
        </div>
      </Card>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Left Column: Citas -->
      <Card class="relative overflow-hidden">
        <div class="absolute -right-6 -top-6 opacity-[0.03]">
          <CalendarDaysIcon class="w-40 h-40" />
        </div>
        
        <div class="flex items-center justify-between mb-8">
          <div>
            <h2 class="text-xl font-black uppercase italic tracking-tighter" :style="{ color: 'var(--text-primary)' }">
               Próximas Citas
            </h2>
            <div class="h-1.5 w-12 mt-1 rounded-full" :style="{ backgroundColor: 'var(--color-primary)' }"></div>
          </div>
          <span class="text-[10px] font-black uppercase tracking-[0.2em]" :style="{ color: 'var(--text-secondary)' }">Estado: Pendiente</span>
        </div>

        <div v-if="ultimasCitasPendientes.length > 0" class="space-y-4">
          <div 
            v-for="cita in ultimasCitasPendientes" 
            :key="cita.id"
            class="group flex items-center justify-between p-5 rounded-3xl border border-transparent hover:border-indigo-500/30 transition-all hover:scale-[1.01] hover:shadow-xl hover:shadow-indigo-500/5"
            :style="{ backgroundColor: 'var(--bg-secondary)' }"
          >
            <div class="flex items-center gap-5">
              <div class="relative">
                <div class="p-4 rounded-2xl shadow-sm transition-all group-hover:text-white" :style="{ backgroundColor: 'var(--bg-primary)', color: 'var(--color-primary)' }">
                  <ScissorsIcon v-if="!isAdminOrBarbero" class="w-6 h-6" />
                  <UserIcon v-else class="w-6 h-6" />
                </div>
                <div class="absolute -right-1 -bottom-1 w-3 h-3 rounded-full ring-2 animate-pulse" :style="{ backgroundColor: 'var(--color-primary)', ringColor: 'var(--bg-primary)' }"></div>
              </div>
              <div>
                <p class="font-black text-lg leading-tight uppercase italic tracking-tighter" :style="{ color: 'var(--text-primary)' }">
                  <template v-if="!isAdminOrBarbero">
                    Barbero: {{ cita.barbero }}
                  </template>
                  <template v-else>
                    {{ cita.cliente }}
                  </template>
                </p>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                  <Badge variant="primary" class="text-[9px] font-black uppercase tracking-widest px-3 py-1 border-none shadow-sm" :style="{ backgroundColor: 'var(--bg-primary)' ,color: 'var(--text-secondary)' }">{{ cita.primer_servicio }}</Badge>
                  <p class="text-[11px] font-bold flex items-center gap-1.5 uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
                    <CalendarIcon class="w-3.5 h-3.5" />
                    {{ cita.fecha }}
                  </p>
                </div>
                <p v-if="isAdminOrBarbero" class="text-[10px] font-bold mt-1 uppercase tracking-widest" :style="{ color: 'var(--text-secondary)' }">
                  Atendido por: <span :style="{ color: 'var(--color-primary)' }">{{ cita.barbero }}</span>
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button @click="$inertia.visit(route(isAdminOrBarbero ? 'citas-admin.show' : 'citas-cliente.show', cita.id))" class="p-3 rounded-2xl shadow-sm opacity-0 group-hover:opacity-100 transition-all hover:scale-110 active:scale-95" :style="{ backgroundColor: 'var(--bg-primary)' }">
                <ChevronRightIcon class="w-5 h-5 transition-colors" :style="{ color: 'var(--text-secondary)' }" />
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-20 rounded-3xl border-2 border-dashed" :style="{ backgroundColor: 'rgba(var(--text-secondary-rgb, 107, 114, 128), 0.05)', borderColor: 'var(--bg-secondary)' }">
          <CalendarDaysIcon class="w-12 h-12 mx-auto mb-4 opacity-20" :style="{ color: 'var(--text-primary)' }" />
          <p class="font-black uppercase tracking-widest italic tracking-tighter" :style="{ color: 'var(--text-secondary)' }">No hay citas en el horizonte</p>
        </div>
      </Card>

      <!-- Right Column: Quick Links -->
      <Card>
        <div class="flex items-center justify-between mb-8">
          <div>
            <h2 class="text-xl font-black uppercase italic tracking-tighter" :style="{ color: 'var(--text-primary)' }">
              Panel de Acceso
            </h2>
            <div class="h-1.5 w-12 mt-1 rounded-full" :style="{ backgroundColor: '#10b981' }"></div>
          </div>
          <span class="text-[10px] font-black uppercase tracking-[0.2em] underline offset-2 cursor-pointer" :style="{ color: 'var(--text-secondary)', textDecorationColor: 'var(--color-primary)' }">Configuración</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- LINKS CLIENTE -->
          <template v-if="!isAdminOrBarbero">
            <Link :href="route('citas-cliente.create')" class="quick-link group shadow-indigo-600/20" :style="{ backgroundImage: 'linear-gradient(to bottom right, var(--color-primary), #4338ca)' }">
              <PlusCircleIcon class="w-10 h-10 mb-2 transition-transform group-hover:rotate-90 group-hover:scale-110" />
              <span>Agendar</span>
              <p class="text-[8px] font-normal opacity-60 mt-1 normal-case tracking-normal">Busca tu barbero ideal</p>
            </Link>
            <Link :href="route('citas-cliente.index')" class="quick-link group shadow-blue-600/20" :style="{ backgroundImage: 'linear-gradient(to bottom right, #2563eb, #1e40af)' }">
              <CalendarDaysIcon class="w-10 h-10 mb-2 transition-transform group-hover:scale-110" />
              <span>Mis Citas</span>
              <p class="text-[8px] font-normal opacity-60 mt-1 normal-case tracking-normal">Revisa tu historial</p>
            </Link>
          </template>

          <!-- LINKS ADMIN / BARBERO -->
          <template v-else>
            <Link :href="route('citas-admin.index')" class="quick-link group shadow-indigo-600/20" :style="{ backgroundImage: 'linear-gradient(to bottom right, var(--color-primary), #4338ca)' }">
              <CalendarDaysIcon class="w-10 h-10 mb-2 group-hover:scale-110" />
              <span>Agenda</span>
            </Link>
            <Link :href="route('products.index')" class="quick-link group shadow-emerald-600/20" :style="{ backgroundImage: 'linear-gradient(to bottom right, #10b981, #059669)' }">
              <ShoppingBagIcon class="w-10 h-10 mb-2 group-hover:scale-110" />
              <span>Productos</span>
            </Link>
            <Link :href="route('services.index')" class="quick-link group shadow-amber-600/20" :style="{ backgroundImage: 'linear-gradient(to bottom right, #f59e0b, #d97706)' }">
              <SparklesIcon class="w-10 h-10 mb-2 group-hover:scale-110" />
              <span>Servicios</span>
            </Link>
            <Link :href="route('movimientos.index')" class="quick-link group shadow-purple-600/20" :style="{ backgroundImage: 'linear-gradient(to bottom right, #8b5cf6, #7c3aed)' }">
              <ArrowsRightLeftIcon class="w-10 h-10 mb-2 group-hover:scale-110" />
              <span>Flujos</span>
            </Link>
            <Link :href="route('bi.v2')" class="quick-link col-span-2 group shadow-slate-900/20" :style="{ backgroundImage: 'linear-gradient(to bottom right, #1e293b, #0f172a)' }">
              <div class="flex items-center gap-4">
                <ChartBarIcon class="w-10 h-10 group-hover:scale-110" :style="{ color: 'var(--color-primary)' }" />
                <div class="text-left">
                  <span class="block">Estadísticas Avanzadas</span>
                  <p class="text-[9px] font-normal opacity-50 normal-case tracking-normal">Análisis BI v2.0 - Reportes Pro</p>
                </div>
              </div>
            </Link>
          </template>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { 
  CalendarDaysIcon, PlusCircleIcon, UserGroupIcon, SparklesIcon, ShoppingBagIcon, 
  CalendarIcon, ClockIcon, CheckCircleIcon, XCircleIcon, ScissorsIcon,
  ChevronRightIcon, UserIcon, ArrowsRightLeftIcon, ChartBarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  metrics: { type: Object, required: true },
  ultimasCitasPendientes: { type: Array, default: () => [] }
});

const page = usePage();
const userRole = computed(() => page.props.auth.user.rol);

const isAdminOrBarbero = computed(() => {
  return ['barbero', 'propietario'].includes(userRole.value);
});
</script>

<style scoped>
.quick-link {
  @apply flex flex-col items-center justify-center p-6 rounded-[2rem] text-white transition-all hover:scale-[1.03] active:scale-95 shadow-lg font-black uppercase text-[11px] tracking-widest text-center;
}
</style>
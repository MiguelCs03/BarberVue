<template>
  <Head title="Dashboard" />

  <AppLayout>
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-black tracking-tighter text-gray-900 dark:text-white uppercase italic">
        Dashboard 
      </h1>
      <p class="text-sm font-bold text-gray-500 dark:text-gray-400 mt-2 uppercase tracking-[0.3em] opacity-70">
        Bienvenido de nuevo, {{ $page.props.auth.user.name }}
      </p>
    </div>

    <!-- Stats Cards: ADMIN / BARBERO -->
    <div v-if="isAdminOrBarbero" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Usuarios -->
      <Card class="hover:scale-[1.02] transition-transform duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-1">Comunidad</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white">{{ metrics.usuarios.total }}</p>
            <div class="flex gap-2 mt-2 text-[9px] font-bold uppercase tracking-wider">
              <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-500 rounded-full">Clientes: {{ metrics.usuarios.clientes }}</span>
              <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 rounded-full">Barberos: {{ metrics.usuarios.barberos }}</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl bg-indigo-500/10 text-indigo-600 shadow-inner">
            <UserGroupIcon class="w-8 h-8" />
          </div>
        </div>
      </Card>

      <!-- Productos -->
      <Card class="hover:scale-[1.02] transition-transform duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-1">Inventario</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white">{{ metrics.productos.total }}</p>
            <div class="flex gap-2 mt-2 text-[9px] font-bold uppercase tracking-wider">
              <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 rounded-full">Activos: {{ metrics.productos.activos }}</span>
              <span class="px-2 py-0.5 bg-rose-500/10 text-rose-500 rounded-full">Inactivos: {{ metrics.productos.inactivos }}</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-600 shadow-inner">
            <ShoppingBagIcon class="w-8 h-8" />
          </div>
        </div>
      </Card>

      <!-- Servicios -->
      <Card class="hover:scale-[1.02] transition-transform duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-1">Catálogo</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white">{{ metrics.servicios.total }}</p>
            <div class="flex gap-2 mt-2 text-[9px] font-bold uppercase tracking-wider">
              <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 rounded-full">Activos: {{ metrics.servicios.activos }}</span>
              <span class="px-2 py-0.5 bg-rose-500/10 text-rose-500 rounded-full">Inactivos: {{ metrics.servicios.inactivos }}</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl bg-amber-500/10 text-amber-600 shadow-inner">
            <SparklesIcon class="w-8 h-8" />
          </div>
        </div>
      </Card>

      <!-- Citas Totales -->
      <Card class="hover:scale-[1.02] transition-transform duration-300 border-l-4 border-indigo-600">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600 mb-1">Operaciones</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white">{{ metrics.citas.total }}</p>
            <div class="flex gap-2 mt-2 text-[9px] font-bold uppercase tracking-wider">
              <span class="px-2 py-0.5 bg-blue-500/10 text-blue-500 rounded-full">Pdt: {{ metrics.citas.pendientes }}</span>
              <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 rounded-full">Conf: {{ metrics.citas.confirmadas }}</span>
            </div>
          </div>
          <div class="p-4 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
            <CalendarDaysIcon class="w-8 h-8" />
          </div>
        </div>
      </Card>
    </div>

    <!-- Stats Cards: CLIENTE -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <Card class="border-b-4 border-blue-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-black uppercase tracking-widest text-blue-500 mb-1">Citas Pendientes</p>
            <p class="text-4xl font-black text-gray-900 dark:text-white">{{ metrics.citas.pendientes }}</p>
          </div>
          <div class="p-4 rounded-2xl bg-blue-500/10 text-blue-600"><ClockIcon class="w-10 h-10" /></div>
        </div>
      </Card>

      <Card class="border-b-4 border-emerald-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-black uppercase tracking-widest text-emerald-500 mb-1">Citas Realizadas</p>
            <p class="text-4xl font-black text-gray-900 dark:text-white">{{ metrics.citas.confirmadas }}</p>
          </div>
          <div class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-600"><CheckCircleIcon class="w-10 h-10" /></div>
        </div>
      </Card>

      <Card class="border-b-4 border-rose-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-black uppercase tracking-widest text-rose-500 mb-1">Canceladas</p>
            <p class="text-4xl font-black text-gray-900 dark:text-white">{{ metrics.citas.canceladas }}</p>
          </div>
          <div class="p-4 rounded-2xl bg-rose-500/10 text-rose-600"><XCircleIcon class="w-10 h-10" /></div>
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
            <h2 class="text-xl font-black uppercase italic tracking-tighter text-gray-900 dark:text-white">
               Próximas Citas
            </h2>
            <div class="h-1.5 w-12 bg-indigo-600 mt-1 rounded-full"></div>
          </div>
          <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Estado: Pendiente</span>
        </div>

        <div v-if="ultimasCitasPendientes.length > 0" class="space-y-4">
          <div 
            v-for="cita in ultimasCitasPendientes" 
            :key="cita.id"
            class="group flex items-center justify-between p-5 rounded-3xl bg-gray-50/50 dark:bg-white/5 border border-transparent hover:border-indigo-500/30 transition-all hover:scale-[1.01] hover:shadow-xl hover:shadow-indigo-500/5"
          >
            <div class="flex items-center gap-5">
              <div class="relative">
                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 text-indigo-600 shadow-sm transition-all group-hover:bg-indigo-600 group-hover:text-white">
                  <ScissorsIcon v-if="!isAdminOrBarbero" class="w-6 h-6" />
                  <UserIcon v-else class="w-6 h-6" />
                </div>
                <div class="absolute -right-1 -bottom-1 w-3 h-3 bg-indigo-500 rounded-full ring-2 ring-white dark:ring-gray-900 animate-pulse"></div>
              </div>
              <div>
                <p class="font-black text-gray-900 dark:text-white text-lg leading-tight uppercase italic tracking-tighter">
                  <template v-if="!isAdminOrBarbero">
                    Barbero: {{ cita.barbero }}
                  </template>
                  <template v-else>
                    {{ cita.cliente }}
                  </template>
                </p>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                  <Badge variant="primary" class="text-[9px] font-black uppercase tracking-widest px-3 py-1 bg-white dark:bg-gray-800 border-none shadow-sm">{{ cita.primer_servicio }}</Badge>
                  <p class="text-[11px] font-bold text-gray-500 flex items-center gap-1.5 uppercase tracking-wider">
                    <CalendarIcon class="w-3.5 h-3.5" />
                    {{ cita.fecha }}
                  </p>
                </div>
                <p v-if="isAdminOrBarbero" class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-widest">
                  Atendido por: <span class="text-indigo-500">{{ cita.barbero }}</span>
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button @click="$inertia.visit(route(isAdminOrBarbero ? 'citas-admin.show' : 'citas-cliente.show', cita.id))" class="p-3 rounded-2xl bg-white dark:bg-gray-800 shadow-sm opacity-0 group-hover:opacity-100 transition-all hover:scale-110 active:scale-95">
                <ChevronRightIcon class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" />
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-20 bg-gray-50/50 dark:bg-white/5 rounded-3xl border-2 border-dashed border-gray-100 dark:border-gray-800">
          <CalendarDaysIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
          <p class="font-black text-gray-400 uppercase tracking-widest italic tracking-tighter">No hay citas en el horizonte</p>
        </div>
      </Card>

      <!-- Right Column: Quick Links -->
      <Card>
        <div class="flex items-center justify-between mb-8">
          <div>
            <h2 class="text-xl font-black uppercase italic tracking-tighter text-gray-900 dark:text-white">
              Panel de Acceso
            </h2>
            <div class="h-1.5 w-12 bg-emerald-500 mt-1 rounded-full"></div>
          </div>
          <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 underline decoration-indigo-500 offset-2 cursor-pointer">Configuración</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- LINKS CLIENTE -->
          <template v-if="!isAdminOrBarbero">
            <Link :href="route('citas-cliente.create')" class="quick-link group from-indigo-600 to-indigo-800 shadow-indigo-600/20">
              <PlusCircleIcon class="w-10 h-10 mb-2 transition-transform group-hover:rotate-90 group-hover:scale-110" />
              <span>Agendar</span>
              <p class="text-[8px] font-normal opacity-60 mt-1 normal-case tracking-normal">Busca tu barbero ideal</p>
            </Link>
            <Link :href="route('citas-cliente.index')" class="quick-link group from-blue-600 to-blue-800 shadow-blue-600/20">
              <CalendarDaysIcon class="w-10 h-10 mb-2 transition-transform group-hover:scale-110" />
              <span>Mis Citas</span>
              <p class="text-[8px] font-normal opacity-60 mt-1 normal-case tracking-normal">Revisa tu historial</p>
            </Link>
          </template>

          <!-- LINKS ADMIN / BARBERO -->
          <template v-else>
            <Link :href="route('citas-admin.index')" class="quick-link group from-indigo-600 to-indigo-800 shadow-indigo-600/20">
              <CalendarDaysIcon class="w-10 h-10 mb-2 group-hover:scale-110" />
              <span>Agenda</span>
            </Link>
            <Link :href="route('products.index')" class="quick-link group from-emerald-600 to-emerald-800 shadow-emerald-600/20">
              <ShoppingBagIcon class="w-10 h-10 mb-2 group-hover:scale-110" />
              <span>Productos</span>
            </Link>
            <Link :href="route('services.index')" class="quick-link group from-amber-600 to-amber-800 shadow-amber-600/20">
              <SparklesIcon class="w-10 h-10 mb-2 group-hover:scale-110" />
              <span>Servicios</span>
            </Link>
            <Link :href="route('movimientos.index')" class="quick-link group from-purple-600 to-purple-800 shadow-purple-600/20">
              <ArrowsRightLeftIcon class="w-10 h-10 mb-2 group-hover:scale-110" />
              <span>Flujos</span>
            </Link>
            <Link :href="route('bi.v2')" class="quick-link col-span-2 group from-slate-800 to-slate-900 shadow-slate-900/20">
              <div class="flex items-center gap-4">
                <ChartBarIcon class="w-10 h-10 group-hover:scale-110 text-indigo-400" />
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
  @apply flex flex-col items-center justify-center p-6 rounded-[2rem] text-white transition-all hover:scale-[1.03] active:scale-95 shadow-lg bg-gradient-to-br font-black uppercase text-[11px] tracking-widest text-center;
}
</style>
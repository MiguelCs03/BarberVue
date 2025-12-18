<template>
  <div 
    class="relative overflow-hidden rounded-2xl p-6 text-white shadow-xl transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl cursor-default group"
    :class="gradiente"
  >
    <!-- Decoraciones de fondo -->
    <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 blur-2xl group-hover:bg-white/20 transition-all"></div>
    <div class="absolute -bottom-6 -left-6 h-20 w-20 rounded-full bg-white/10 blur-xl"></div>

    <div class="relative z-10 flex items-center justify-between">
      <div class="space-y-2">
        <p class="text-xs font-black uppercase tracking-[0.2em] opacity-80">{{ title }}</p>
        <h4 class="text-4xl font-black tracking-tighter">{{ value }}</h4>
        
        <div class="flex items-center gap-2 pt-1">
          <div 
            class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
            :class="trend === 'up' ? 'bg-emerald-400/20 text-emerald-100' : 'bg-rose-400/20 text-rose-100'"
          >
            <component 
              :is="trend === 'up' ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" 
              class="w-3 h-3"
            />
            {{ percentage }}%
          </div>
          <span class="text-[10px] font-medium opacity-60">vs anterior</span>
        </div>
      </div>

      <div class="bg-white/20 p-4 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner group-hover:rotate-6 transition-transform">
        <component :is="iconComponent" class="w-8 h-8 text-white" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  ArrowTrendingUpIcon, 
  ArrowTrendingDownIcon,
  CurrencyDollarIcon,
  CalendarDaysIcon,
  UserGroupIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/solid';
import { computed } from 'vue';

const props = defineProps({
  title: String,
  value: String,
  percentage: [String, Number],
  trend: String,
  icon: String,
  gradiente: {
    type: String,
    default: 'bg-gradient-to-br from-indigo-500 to-indigo-700'
  }
});

const iconComponent = computed(() => {
  switch (props.icon) {
    case 'ingresos': return CurrencyDollarIcon;
    case 'reservas': return CalendarDaysIcon;
    case 'ocupacion': return UserGroupIcon;
    case 'satisfaccion': return ExclamationTriangleIcon;
    default: return CurrencyDollarIcon;
  }
});
</script>

<style scoped>
/* Extra refinements */
.group:hover .bg-white\/20 {
  background-color: rgba(255, 255, 255, 0.3);
}
</style>

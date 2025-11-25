<template>
  <div class="min-h-screen flex flex-col">
    <!-- Top Navigation Bar -->
    <nav class="shadow-md sticky top-0 z-40" :style="{ backgroundColor: 'var(--bg-secondary)' }">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 gap-4">
          <!-- Logo -->
          <div class="flex items-center gap-3 flex-shrink-0">
            <div class="text-3xl">💈</div>
            <h1 class="text-2xl font-bold hidden sm:block" :style="{ color: 'var(--color-primary)' }">
              BarberShop
            </h1>
          </div>

          <!-- Global Search -->
          <div class="flex-1 max-w-xl hidden md:block">
            <GlobalSearch />
          </div>

          <!-- Right Side Controls -->
          <div class="flex items-center gap-4 flex-shrink-0">
            <AccessibilityControls />
            <ThemeSwitcher />
          </div>
        </div>
      </div>
    </nav>
    
    <!-- Main Content Area -->
    <div class="flex flex-1">
      <!-- Sidebar Navigation -->
      <aside 
        class="w-64 shadow-lg hidden lg:block"
        :style="{ backgroundColor: 'var(--bg-secondary)' }"
      >
        <nav class="p-4 space-y-2">
          
          <!-- Dynamic Menu Items -->
          <template v-for="item in menuItems" :key="item.id">
            <!-- Parent Item (with or without children) -->
            <div v-if="item.children && item.children.length > 0">
              <!-- Section Header -->
              <div class="pt-4 pb-2 px-4 text-xs font-semibold uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
                {{ item.nombre }}
              </div>
              
              <!-- Children -->
              <Link
                v-for="child in item.children"
                :key="child.id"
                :href="child.ruta"
                class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all"
                :class="isActive(child.ruta) ? 'font-bold' : ''"
                :style="isActive(child.ruta) ? { backgroundColor: 'var(--color-primary)', color: 'white' } : { color: 'var(--text-primary)' }"
              >
                <MenuIcon :name="child.icono" />
                <span>{{ child.nombre }}</span>
              </Link>
            </div>

            <!-- Single Item (no children) -->
            <Link
              v-else
              :href="item.ruta"
              class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all"
              :class="isActive(item.ruta) ? 'font-bold' : ''"
              :style="isActive(item.ruta) ? { backgroundColor: 'var(--color-primary)', color: 'white' } : { color: 'var(--text-primary)' }"
            >
              <MenuIcon :name="item.icono" />
              <span>{{ item.nombre }}</span>
            </Link>
          </template>
        </nav>
        <!-- Logout Button -->
        <div class="p-4 border-t" :style="{ borderColor: 'var(--border-color)' }">
          <button
            @click="logout"
            class="flex items-center gap-3 w-full px-4 py-3 rounded-lg transition-all hover:bg-red-50 dark:hover:bg-red-900/20 group"
          >
            <ArrowLeftStartOnRectangleIcon
              class="w-5 h-5 flex-shrink-0 text-red-500 group-hover:text-red-600 transition-colors" 
            />
            <span class="font-medium text-red-500 group-hover:text-red-600 transition-colors">
              Cerrar Sesión
            </span>
          </button>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-6 lg:p-8">
        <slot />
      </main>
    </div>

    <!-- Footer -->
    <footer class="shadow-md mt-auto" :style="{ backgroundColor: 'var(--bg-secondary)' }">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-2">
          <p class="text-center text-sm" :style="{ color: 'var(--text-secondary)' }">
            © {{ currentYear }} BarberShop - Sistema de Gestión
          </p>
          
          <!-- Visit Counter -->
          <div class="flex items-center gap-2 text-sm" :style="{ color: 'var(--text-secondary)' }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span>
              Visitas a esta página: <strong :style="{ color: 'var(--color-primary)' }">{{ currentPageVisits }}</strong>
            </span>
            <span class="hidden sm:inline">|</span>
            <span class="hidden sm:inline">
              Total: <strong :style="{ color: 'var(--color-primary)' }">{{ totalVisits }}</strong>
            </span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import { Link, usePage,router } from '@inertiajs/vue3';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import AccessibilityControls from '@/Components/AccessibilityControls.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import MenuIcon from '@/Components/MenuIcon.vue';
import { useTheme } from '@/composables/useTheme';
import { useAccessibility } from '@/composables/useAccessibility';
import { usePageVisits } from '@/composables/usePageVisits';
import { ArrowLeftStartOnRectangleIcon } from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';
const page = usePage();
const currentYear = new Date().getFullYear();

// Get menu items from shared data
const menuItems = computed(() => page.props.menuItems || []);

const { initializeTheme } = useTheme();
const { initializeAccessibility } = useAccessibility();
const { 
  initializePageVisits, 
  trackPageVisit, 
  currentPageVisits,
  totalVisits 
} = usePageVisits();

// Get current page name from URL
const getCurrentPageName = () => {
  const url = page.url;
  
  // Map URLs to page names
  if (url === '/dashboard' || url === '/') return 'dashboard';
  if (url === '/users') return 'users.index';
  if (url === '/users/create') return 'users.create';
  if (url.match(/\/users\/\d+\/edit/)) return 'users.edit';
  if (url.match(/\/users\/\d+/)) return 'users.show';
  if (url === '/products') return 'products.index';
  if (url === '/products/create') return 'products.create';
  if (url.match(/\/products\/\d+\/edit/)) return 'products.edit';
  if (url.match(/\/products\/\d+/)) return 'products.show';
  if (url === '/profile') return 'profile.edit';
  if (url === '/analytics/visits') return 'analytics.visits';
  if (url === '/theme-test') return 'theme.test';
  
  return url.replace(/^\//, '').replace(/\//g, '.');
};

// Initialize theme and accessibility on component mount
onMounted(() => {
  initializeTheme();
  initializeAccessibility();
  initializePageVisits();
  
  // Track initial page visit
  const pageName = getCurrentPageName();
  trackPageVisit(pageName);
});

// Track page visits when URL changes (for SPA navigation)
watch(() => page.url, () => {
  const pageName = getCurrentPageName();
  trackPageVisit(pageName);
});

watch(() => page.props.flash, (newFlash) => {
  // Si no hay flash o está vacío, no hacemos nada
  if (!newFlash) return;

  // 1. Mensaje de Éxito
  if (newFlash.success) {
    Swal.fire({
      icon: 'success',
      title: '¡Éxito!',
      text: newFlash.success,
      timer: 3000,
      showConfirmButton: false,
      background: 'var(--bg-primary)',
      color: 'var(--text-primary)'
    });
    // IMPORTANTE: Limpiar el mensaje para que no salga al navegar atrás
    page.props.flash.success = null; 
  }

  // 2. Mensaje de Error
  if (newFlash.error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: newFlash.error,
      confirmButtonColor: '#EF4444',
      background: 'var(--bg-primary)',
      color: 'var(--text-primary)'
    });
    // IMPORTANTE: Limpiar el mensaje
    //page.props.flash.error = null;
  }
}, { deep: true, immediate: true });

const isActive = (routeName) => {
  const currentRoute = page.url;
  
  // Handle wildcard routes
  if (routeName.includes('*')) {
    const baseRoute = routeName.replace('.*', '');
    return currentRoute.startsWith(`/${baseRoute}`);
  }
  
  // Exact match
  return currentRoute === `/${routeName}` || currentRoute === routeName;
};
const logout = () => {
  router.post(route('logout'));
};
</script>

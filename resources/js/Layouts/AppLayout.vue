<template>
  <div class="min-h-screen flex flex-col">
    <!-- Top Navigation Bar -->
    <nav class="shadow-md sticky top-0 z-40" :style="{ backgroundColor: 'var(--bg-secondary)' }">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <div class="flex items-center gap-3">
            <div class="text-3xl">💈</div>
            <h1 class="text-2xl font-bold" :style="{ color: 'var(--color-primary)' }">
              BarberShop
            </h1>
          </div>

          <!-- Right Side Controls -->
          <div class="flex items-center gap-4">
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
          <Link
            :href="route('dashboard')"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all"
            :class="isActive('dashboard') ? 'font-bold' : ''"
            :style="isActive('dashboard') ? { backgroundColor: 'var(--color-primary)', color: 'white' } : { color: 'var(--text-primary)' }"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
          </Link>

          <Link
            :href="route('users.index')"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all"
            :class="isActive('users.*') ? 'font-bold' : ''"
            :style="isActive('users.*') ? { backgroundColor: 'var(--color-primary)', color: 'white' } : { color: 'var(--text-primary)' }"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Usuarios</span>
          </Link>

          <!-- Placeholder for future modules -->
          <div class="pt-4 pb-2 px-4 text-xs font-semibold uppercase tracking-wider" :style="{ color: 'var(--text-secondary)' }">
            Próximamente
          </div>

          <div class="flex items-center gap-3 px-4 py-3 rounded-lg opacity-50 cursor-not-allowed" :style="{ color: 'var(--text-primary)' }">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span>Productos</span>
          </div>

          <div class="flex items-center gap-3 px-4 py-3 rounded-lg opacity-50 cursor-not-allowed" :style="{ color: 'var(--text-primary)' }">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Servicios</span>
          </div>

          <div class="flex items-center gap-3 px-4 py-3 rounded-lg opacity-50 cursor-not-allowed" :style="{ color: 'var(--text-primary)' }">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Citas</span>
          </div>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-6 lg:p-8">
        <slot />
      </main>
    </div>

    <!-- Footer -->
    <footer class="shadow-md mt-auto" :style="{ backgroundColor: 'var(--bg-secondary)' }">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <p class="text-center text-sm" :style="{ color: 'var(--text-secondary)' }">
          © {{ currentYear }} BarberShop - Sistema de Gestión
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import AccessibilityControls from '@/Components/AccessibilityControls.vue';
import { useTheme } from '@/composables/useTheme';
import { useAccessibility } from '@/composables/useAccessibility';

const page = usePage();
const currentYear = new Date().getFullYear();

const { initializeTheme } = useTheme();
const { initializeAccessibility } = useAccessibility();

// Initialize theme and accessibility on component mount
onMounted(() => {
  initializeTheme();
  initializeAccessibility();
});

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
</script>

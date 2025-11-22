<template>
  <Head title="Test de Temas" />

  <div class="min-h-screen p-8">
    <h1 class="text-3xl font-bold mb-6">Página de Prueba de Temas</h1>
    
    <ThemeDebug />
    
    <div class="mt-8">
      <Link :href="route('users.index')" class="text-blue-600 underline">
        Volver a Usuarios
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ThemeDebug from '@/Components/ThemeDebug.vue';
</script>

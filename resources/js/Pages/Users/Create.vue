<template>
  <Head title="Crear Usuario" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('users.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver a la lista
      </Link>
      <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">Nuevo Usuario</h1>
      <p :style="{ color: 'var(--text-secondary)' }">Registra un nuevo miembro del equipo o cliente.</p>
    </div>

    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
          <Card>
            <div class="flex items-center gap-2 mb-6 border-b pb-4 dark:border-gray-700">
              <svg class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">Datos Personales</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="text-sm font-semibold uppercase tracking-wider text-gray-500">Nombre *</label>
                <input v-model="form.name" type="text" required class="input w-full" placeholder="Juan" />
              </div>
              <div class="space-y-1">
                <label class="text-sm font-semibold uppercase tracking-wider text-gray-500">Apellido</label>
                <input v-model="form.apellido" type="text" class="input w-full" placeholder="Pérez" />
              </div>
              <div class="space-y-1 md:col-span-2">
                <label class="text-sm font-semibold uppercase tracking-wider text-gray-500">Correo Electrónico *</label>
                <input v-model="form.email" type="email" required class="input w-full" placeholder="ejemplo@barberia.com" />
              </div>
              <div class="space-y-1">
                <label class="text-sm font-semibold uppercase tracking-wider text-gray-500">Teléfono</label>
                <input v-model="form.telefono" type="text" class="input w-full" placeholder="+591 ..." />
              </div>
            </div>
          </Card>

          <Card v-if="form.rol === 'barbero'">
            <div class="flex items-center justify-between mb-6 border-b pb-4 dark:border-gray-700">
              <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">Servicios Asignados</h2>
              <span class="text-xs font-bold px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 uppercase tracking-tighter">
                {{ form.servicios.length }} Seleccionados
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
              <div 
                v-for="servicio in servicios" :key="servicio.id"
                @click="toggleServicio(servicio.id)"
                :class="[
                  'relative p-4 rounded-xl border-2 cursor-pointer transition-all duration-300 group',
                  form.servicios.includes(servicio.id) 
                    ? 'border-[var(--color-primary)] bg-[var(--color-primary)]/[0.08]' 
                    : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900/50'
                ]"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <p class="font-bold text-sm" :class="form.servicios.includes(servicio.id) ? 'text-[var(--color-primary)]' : 'text-[var(--text-primary)]'">
                      {{ servicio.nombre }}
                    </p>
                    <p class="mt-2 font-bold text-sm">Bs. {{ servicio.precio }}</p>
                  </div>
                  <div 
                    class="w-5 h-5 rounded border-2 flex items-center justify-center transition-all"
                    :class="form.servicios.includes(servicio.id) ? 'bg-[var(--color-primary)] border-[var(--color-primary)]' : 'border-gray-300 dark:border-gray-600'"
                  >
                    <svg v-if="form.servicios.includes(servicio.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </Card>
        </div>

        <div class="space-y-6">
          <Card>
            <h3 class="font-bold mb-4 uppercase text-xs text-gray-400">Acceso</h3>
            <div class="space-y-4">
              <div>
                <label class="text-xs font-bold mb-1 block">Rol del Usuario *</label>
                <select v-model="form.rol" required class="input w-full">
                  <option value="" disabled>Seleccione un rol</option>
                  <option value="barbero">Barbero</option>
                  <option value="cliente">Cliente</option>
                  <option value="administrador">Administrador</option>
                </select>
              </div>
              <div class="pt-4 border-t dark:border-gray-700">
                <label class="text-xs font-bold mb-1 block">Contraseña *</label>
                <input v-model="form.password" type="password" required class="input w-full mb-3" />
                <label class="text-xs font-bold mb-1 block">Confirmar Contraseña *</label>
                <input v-model="form.password_confirmation" type="password" required class="input w-full" />
              </div>
            </div>
          </Card>

          <button 
            type="submit" 
            class="btn-primary w-full py-4 shadow-lg shadow-[var(--color-primary)]/20 text-lg font-bold"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Registrando...' : 'Registrar Usuario' }}
          </button>
        </div>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  servicios: Array
});

const form = useForm({
  name: '',
  apellido: '',
  email: '',
  telefono: '',
  password: '',
  password_confirmation: '',
  rol: '',
  servicios: [],
});

const toggleServicio = (id) => {
  const index = form.servicios.indexOf(id);
  if (index > -1) form.servicios.splice(index, 1);
  else form.servicios.push(id);
};

const handleSubmit = () => {
  form.post(route('users.store'));
};
</script>
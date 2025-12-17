<template>
  <Head :title="`Editar: ${user.name}`" />

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
      <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">Edición de Usuario</h1>
      <p :style="{ color: 'var(--text-secondary)' }">Modifica el perfil y los servicios asignados.</p>
    </div>

    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
          <Card>
            <div class="flex items-center gap-2 mb-6 border-b pb-4">
              <svg class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <h2 class="text-xl font-bold">Información Personal</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="text-sm font-semibold uppercase tracking-wider text-gray-500">Nombre *</label>
                <input v-model="form.name" type="text" required class="input w-full" />
                <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
              </div>

              <div class="space-y-1">
                <label class="text-sm font-semibold uppercase tracking-wider text-gray-500">Apellido</label>
                <input v-model="form.apellido" type="text" class="input w-full" placeholder="introduzca su apellido"/>
              </div>

              <div class="space-y-1">
                <label class="text-sm font-semibold uppercase tracking-wider text-gray-500">Email *</label>
                <input v-model="form.email" type="email" required class="input w-full" placeholder="introduzca su email"/>
              </div>

              <div class="space-y-1">
                <label class="text-sm font-semibold uppercase tracking-wider text-gray-500">Teléfono</label>
                <input v-model="form.telefono" type="text" class="input w-full" placeholder="+591 ..." />
              </div>
            </div>
          </Card>

          <!-- <Card v-if="form.rol === 'barbero'">
            <div class="flex items-center justify-between mb-6 border-b pb-4">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758L5 19m0-14l4.121 4.121" />
                </svg>
                <h2 class="text-xl font-bold">Servicios Habilitados</h2>
              </div>
              <span class="text-xs font-bold px-2 py-1 rounded bg-gray-100 uppercase">{{ form.servicios.length }} Seleccionados</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[400px] overflow-y-auto pr-2">
              <div 
                v-for="servicio in servicios" 
                :key="servicio.id"
                @click="toggleServicio(servicio.id)"
                :class="[
                  'relative p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 select-none',
                  form.servicios.includes(servicio.id) 
                    ? 'border-[var(--color-primary)] bg-[var(--color-primary)] bg-opacity-5' 
                    : 'border-transparent bg-gray-50 hover:bg-gray-100'
                ]"
              >
                <div class="flex justify-between items-start">
                  <div class="pr-8">
                    <p class="font-bold text-sm" :style="{ color: 'var(--text-primary)' }">{{ servicio.nombre }}</p>
                    <p class="text-xs text-gray-500 line-clamp-1">{{ servicio.descripcion }}</p>
                    <p class="mt-2 font-bold text-[var(--color-primary)] text-sm">Bs. {{ servicio.precio }}</p>
                  </div>
                  <div 
                    class="absolute top-4 right-4 w-5 h-5 rounded-full border-2 flex items-center justify-center"
                    :class="form.servicios.includes(servicio.id) ? 'bg-[var(--color-primary)] border-[var(--color-primary)]' : 'border-gray-300'"
                  >
                    <svg v-if="form.servicios.includes(servicio.id)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                    </svg>
                  </div>
                </div>
              </div>
            </div>
            <p v-if="form.errors.servicios" class="text-sm text-red-600 mt-3">{{ form.errors.servicios }}</p>
          </Card> -->
          <Card v-if="form.rol === 'barbero'">
            <div class="flex items-center justify-between mb-6 border-b pb-4 border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" :style="{ color: 'var(--color-primary)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758L5 19m0-14l4.121 4.121" />
                </svg>
                <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">Servicios Habilitados</h2>
              </div>
              <span class="text-xs font-bold px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 dark:text-gray-300 uppercase">
                {{ form.servicios.length }} Seleccionados
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
              <div 
                v-for="servicio in servicios" 
                :key="servicio.id"
                @click="toggleServicio(servicio.id)"
                :class="[
      'relative p-4 rounded-xl border-2 cursor-pointer transition-all duration-300 select-none group',
      form.servicios.includes(servicio.id) 
        ? 'border-[var(--color-primary)] bg-[var(--color-primary)]/[0.08] shadow-sm' 
        : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/50 hover:border-gray-300 dark:hover:border-gray-600 shadow-sm'
    ]"
              >
                <div class="flex justify-between items-start">
                  <div class="pr-8">
                    <p 
                      class="font-bold text-sm transition-colors" 
                      :class="form.servicios.includes(servicio.id) ? 'text-[var(--color-primary)]' : 'text-[var(--text-primary)]'"
                    >
                      {{ servicio.nombre }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1 mt-1">
                      {{ servicio.descripcion }}
                    </p>
                    <div class="mt-3 flex items-center gap-2">
                      <span class="text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Precio:</span>
                      <span class="font-bold text-[var(--color-primary)] text-sm">Bs. {{ servicio.precio }}</span>
                    </div>
                  </div>

                  <div 
                    class="absolute top-4 right-4 w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all"
                    :class="form.servicios.includes(servicio.id) 
                      ? 'bg-[var(--color-primary)] border-[var(--color-primary)] shadow-lg shadow-[var(--color-primary)]/20' 
                      : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'"
                  >
                    <svg v-if="form.servicios.includes(servicio.id)" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
            <p v-if="form.errors.servicios" class="text-sm text-red-600 mt-3 font-medium">{{ form.errors.servicios }}</p>
          </Card>
        </div>

        <div class="space-y-6">
          <Card>
            <h3 class="font-bold mb-4 uppercase text-xs tracking-widest text-gray-400">Estado y Rol</h3>
            <div class="space-y-4">
              <div>
                <label class="text-xs font-bold mb-1 block">Estado de Cuenta</label>
                <select v-model="form.estado" class="input w-full shadow-sm">
                  <option value="activo">Activo (Permitir acceso)</option>
                  <option value="inactivo">Inactivo (Bloquear acceso)</option>
                </select>
              </div>
              <div>
                <label class="text-xs font-bold mb-1 block">Rol de Usuario</label>
                <select v-model="form.rol" class="input w-full">
                  <option value="barbero">Barbero</option>
                  <option value="cliente">Cliente</option>
                  <option value="administrador">Administrador</option>
                </select>
              </div>

              <div v-if="form.rol === 'barbero'">
                <label class="text-xs font-bold mb-1 block">Estado Laboral</label>
                <select v-model="form.estado_barbero" class="input w-full">
                  <option value="disponible">Disponible</option>
                  <option value="ocupado">En Servicio</option>
                  <option value="descanso">En Descanso</option>
                </select>
              </div>
            </div>
          </Card>

          <Card>
            <h3 class="font-bold mb-4 uppercase text-xs tracking-widest text-gray-400">Seguridad</h3>
            <div class="space-y-4">
              <div>
                <label class="text-xs font-bold mb-1 block">Nueva Contraseña</label>
                <input v-model="form.password" type="password" class="input w-full" placeholder="Omitir para no cambiar" />
              </div>
              <div v-if="form.password">
                <label class="text-xs font-bold mb-1 block">Confirmar Contraseña</label>
                <input v-model="form.password_confirmation" type="password" class="input w-full" />
              </div>
            </div>
          </Card>

          <div class="flex flex-col gap-2">
            <button 
              type="submit" 
              class="btn-primary w-full justify-center py-3"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Sincronizando...' : 'Actualizar Usuario' }}
            </button>
            <Link :href="route('users.index')" class="btn-secondary w-full justify-center text-center">
              Descartar Cambios
            </Link>
          </div>
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
  user: Object,
  servicios: Array,
});

const form = useForm({
  name: props.user.name,
  apellido: props.user.apellido || '',
  email: props.user.email,
  telefono: props.user.telefono || '',
  estado: props.user.estado || 'activo',
  password: '',
  password_confirmation: '',
  rol: props.user.rol,
  estado_barbero: props.user.estado_barbero || 'disponible',
  servicios: [...props.user.servicios], // Copia para evitar mutaciones directas
});

// Función para mejorar la experiencia de selección (UX)
const toggleServicio = (id) => {
  const index = form.servicios.indexOf(id);
  if (index > -1) {
    form.servicios.splice(index, 1);
  } else {
    form.servicios.push(id);
  }
};

const handleSubmit = () => {
  form.put(route('users.update', props.user.id));
};
</script>
<template>
  <Head title="Crear Usuario" />

  <AppLayout>
    <!-- Page Header -->
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

      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Crear Nuevo Usuario
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Completa el formulario para agregar un nuevo usuario al sistema
      </p>
    </div>

    <!-- Form -->
    <Card>
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Nombre Completo *
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="input"
            placeholder="Ej: Juan Pérez"
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Email *
          </label>
          <input
            v-model="form.email"
            type="email"
            required
            class="input"
            placeholder="usuario@ejemplo.com"
          />
          <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Contraseña *
          </label>
          <input
            v-model="form.password"
            type="password"
            required
            class="input"
            placeholder="Mínimo 8 caracteres"
          />
          <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
        </div>

        <!-- Password Confirmation -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Confirmar Contraseña *
          </label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            class="input"
            placeholder="Repite la contraseña"
          />
        </div>

        <!-- Role -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Rol *
          </label>
          <select
            v-model="form.rol"
            required
            class="input"
          >
            <option value="">Seleccionar rol...</option>
            <option value="barbero">Barbero</option>
            <option value="cliente">Cliente</option>
          </select>
          <p v-if="form.errors.rol" class="mt-1 text-sm text-red-600">{{ form.errors.rol }}</p>
        </div>

        <!-- Estado Barbero (only if barbero) -->
        <div v-if="form.rol === 'barbero'">
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Estado del Barbero
          </label>
          <select
            v-model="form.estado_barbero"
            class="input"
          >
            <option value="disponible">Disponible</option>
            <option value="ocupado">Ocupado</option>
            <option value="descanso">Descanso</option>
          </select>
        </div>

        <!-- Servicios (only if barbero) -->
        <div v-if="form.rol === 'barbero'">
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Servicios que ofrece *
          </label>
          <p class="text-xs mb-3" :style="{ color: 'var(--text-secondary)' }">
            Selecciona los servicios que este barbero puede realizar
          </p>
          
          <div class="grid grid-cols-1 gap-2 max-h-60 overflow-y-auto p-3 rounded-lg" :style="{ backgroundColor: 'var(--bg-secondary)' }">
            <label
              v-for="servicio in servicios"
              :key="servicio.id"
              class="flex items-start gap-3 p-3 rounded-lg cursor-pointer transition-all hover:opacity-80"
              :style="{ 
                backgroundColor: form.servicios.includes(servicio.id) ? 'var(--color-primary)' : 'var(--bg-primary)',
                color: form.servicios.includes(servicio.id) ? 'white' : 'var(--text-primary)'
              }"
            >
              <input
                type="checkbox"
                :value="servicio.id"
                v-model="form.servicios"
                class="mt-1 w-4 h-4 rounded"
                :style="{ accentColor: 'var(--color-primary)' }"
              />
              <div class="flex-1">
                <p class="font-semibold">{{ servicio.nombre }}</p>
                <p class="text-xs opacity-75">{{ servicio.descripcion }}</p>
                <p class="text-sm mt-1">Bs. {{ servicio.precio }}</p>
              </div>
            </label>
          </div>
          <p v-if="form.errors.servicios" class="mt-1 text-sm text-red-600">{{ form.errors.servicios }}</p>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-4">
          <button
            type="submit"
            class="btn-primary"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Creando...' : 'Crear Usuario' }}
          </button>
          <Link
            :href="route('users.index')"
            class="btn-secondary"
          >
            Cancelar
          </Link>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  servicios: {
    type: Array,
    default: () => []
  }
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  rol: '',
  estado_barbero: 'disponible',
  servicios: [],
});

const handleSubmit = () => {
  form.post(route('users.store'), {
    preserveScroll: true,
  });
};
</script>

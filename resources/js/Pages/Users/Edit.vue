<template>
  <Head title="Editar Usuario" />

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
        Editar Usuario
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Actualiza la información del usuario
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
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
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
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
        </div>

        <!-- Phone -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Teléfono *
          </label>
          <input
            v-model="form.phone"
            type="tel"
            required
            class="input"
            placeholder="+591 7123-4567"
          />
          <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
        </div>

        <!-- Role -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Rol *
          </label>
          <select
            v-model="form.role"
            required
            class="input"
          >
            <option value="">Seleccionar rol...</option>
            <option value="propietario">Propietario</option>
            <option value="barbero">Barbero</option>
            <option value="secretaria">Secretaria</option>
            <option value="cliente">Cliente</option>
          </select>
          <p v-if="errors.role" class="mt-1 text-sm text-red-600">{{ errors.role }}</p>
        </div>

        <!-- Avatar (placeholder) -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Foto de Perfil
          </label>
          <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl" :style="{ backgroundColor: 'var(--bg-secondary)' }">
              👤
            </div>
            <button
              type="button"
              class="btn-secondary"
              @click="alert('Funcionalidad de carga de imagen pendiente')"
            >
              Cambiar Imagen
            </button>
          </div>
        </div>

        <!-- Status -->
        <div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="form.status"
              type="checkbox"
              class="rounded"
              :style="{ accentColor: 'var(--color-primary)' }"
            />
            <span class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">
              Usuario activo
            </span>
          </label>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-4">
          <button
            type="submit"
            class="btn-primary"
          >
            Guardar Cambios
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
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

// Get user ID from route (in real app, this would come from props)
const userId = window.location.pathname.split('/').pop();

// Mock user data (in real app, this would come from backend)
const mockUsers = {
  '1': { id: 1, name: 'Juan Pérez', email: 'juan.perez@barbershop.com', phone: '+591 7123-4567', role: 'propietario', status: true },
  '2': { id: 2, name: 'María González', email: 'maria.gonzalez@barbershop.com', phone: '+591 7234-5678', role: 'barbero', status: true },
  '3': { id: 3, name: 'Carlos Rodríguez', email: 'carlos.rodriguez@barbershop.com', phone: '+591 7345-6789', role: 'barbero', status: true },
  '4': { id: 4, name: 'Ana Martínez', email: 'ana.martinez@barbershop.com', phone: '+591 7456-7890', role: 'secretaria', status: true },
  '5': { id: 5, name: 'Pedro Sánchez', email: 'pedro.sanchez@gmail.com', phone: '+591 7567-8901', role: 'cliente', status: true },
  '6': { id: 6, name: 'Laura Torres', email: 'laura.torres@gmail.com', phone: '+591 7678-9012', role: 'cliente', status: false },
};

const currentUser = mockUsers[userId] || mockUsers['1'];

const form = ref({
  name: currentUser.name,
  email: currentUser.email,
  phone: currentUser.phone,
  role: currentUser.role,
  status: currentUser.status,
});

const errors = ref({});

const handleSubmit = () => {
  // Client-side validation
  errors.value = {};

  if (!form.value.name) {
    errors.value.name = 'El nombre es requerido';
  }

  if (!form.value.email) {
    errors.value.email = 'El email es requerido';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'El email no es válido';
  }

  if (!form.value.phone) {
    errors.value.phone = 'El teléfono es requerido';
  }

  if (!form.value.role) {
    errors.value.role = 'El rol es requerido';
  }

  if (Object.keys(errors.value).length === 0) {
    alert('Usuario actualizado exitosamente (funcionalidad de backend pendiente)');
    router.visit(route('users.index'));
  }
};
</script>

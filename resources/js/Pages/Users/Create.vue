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
              Subir Imagen
            </button>
          </div>
          <p class="mt-2 text-sm" :style="{ color: 'var(--text-secondary)' }">
            Formatos aceptados: JPG, PNG (máx. 2MB)
          </p>
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
            Crear Usuario
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

const form = ref({
  name: '',
  email: '',
  phone: '',
  role: '',
  status: true,
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
    alert('Usuario creado exitosamente (funcionalidad de backend pendiente)');
    router.visit(route('users.index'));
  }
};
</script>

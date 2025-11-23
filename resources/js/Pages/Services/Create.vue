<template>
  <Head title="Crear Servicio" />

  <AppLayout>
    <div class="mb-6">
      <Link
        :href="route('services.index')"
        class="inline-flex items-center gap-2 mb-4 hover:opacity-75 transition-opacity"
        :style="{ color: 'var(--color-primary)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver a la lista
      </Link>

      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Crear Nuevo Servicio
      </h1>
    </div>

    <Card>
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Mensajes de error -->
        <div v-if="form.errors && Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
          <ul class="list-disc list-inside">
            <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
          </ul>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Nombre del Servicio *
          </label>
          <input
            v-model="form.nombre"
            type="text"
            required
            class="input"
            :class="{ 'border-red-500': form.errors?.nombre }"
            placeholder="Ej: Corte de cabello"
          />
          <p v-if="form.errors?.nombre" class="text-red-500 text-sm mt-1">{{ form.errors.nombre }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Descripción
          </label>
          <textarea
            v-model="form.descripcion"
            rows="3"
            class="input"
            :class="{ 'border-red-500': form.errors?.descripcion }"
            placeholder="Descripción del servicio (opcional)"
          ></textarea>
          <p v-if="form.errors?.descripcion" class="text-red-500 text-sm mt-1">{{ form.errors.descripcion }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Duración Estimada (minutos)
            </label>
            <input
              v-model.number="form.duracion_estimada"
              type="number"
              min="1"
              class="input"
              :class="{ 'border-red-500': form.errors?.duracion_estimada }"
              placeholder="40 (por defecto)"
            />
            <p class="text-sm mt-1" :style="{ color: 'var(--text-secondary)' }">
              Si no se especifica, será 40 minutos por defecto
            </p>
            <p v-if="form.errors?.duracion_estimada" class="text-red-500 text-sm mt-1">{{ form.errors.duracion_estimada }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Precio (Bs.) *
            </label>
            <input
              v-model.number="form.precio"
              type="number"
              step="0.01"
              min="0"
              required
              class="input"
              :class="{ 'border-red-500': form.errors?.precio }"
              placeholder="0.00"
            />
            <p v-if="form.errors?.precio" class="text-red-500 text-sm mt-1">{{ form.errors.precio }}</p>
          </div>
        </div>

        <div class="flex gap-3 pt-4">
          <button 
            type="submit" 
            class="btn-primary"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Guardando...' : 'Crear Servicio' }}
          </button>
          <Link :href="route('services.index')" class="btn-secondary">
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

const form = useForm({
  nombre: '',
  descripcion: '',
  duracion_estimada: null, // null para que use el default de BD
  precio: 0,
});

const handleSubmit = () => {
  form.post(route('services.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirección manejada por el controller
    },
  });
};
</script>
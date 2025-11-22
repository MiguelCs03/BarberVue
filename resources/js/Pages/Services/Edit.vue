<template>
  <Head title="Editar Servicio" />

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
        Editar Servicio
      </h1>
    </div>

    <Card>
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Nombre del Servicio *
          </label>
          <input
            v-model="form.nombre"
            type="text"
            required
            class="input"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
            Descripción *
          </label>
          <textarea
            v-model="form.descripcion"
            required
            rows="3"
            class="input"
          ></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
              Duración Estimada (minutos) *
            </label>
            <input
              v-model.number="form.duracion_estimada"
              type="number"
              min="1"
              required
              class="input"
            />
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
            />
          </div>
        </div>

        <div class="flex gap-3 pt-4">
          <button type="submit" class="btn-primary">
            Guardar Cambios
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
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const serviceId = window.location.pathname.split('/').filter(Boolean).pop();

const mockServices = {
  '1': { id: 1, nombre: 'Corte de cabello', descripcion: 'Corte clásico de caballero', duracion_estimada: 30, precio: 15.00 },
  '2': { id: 2, nombre: 'Afeitado', descripcion: 'Afeitado con navaja y toalla caliente', duracion_estimada: 20, precio: 10.00 },
  '3': { id: 3, nombre: 'Corte + Barba', descripcion: 'Corte y arreglo de barba', duracion_estimada: 45, precio: 25.00 },
  '4': { id: 4, nombre: 'Coloración', descripcion: 'Tinte y tratamiento', duracion_estimada: 60, precio: 40.00 },
};

const currentService = mockServices[serviceId] || mockServices['1'];

const form = ref({
  nombre: currentService.nombre,
  descripcion: currentService.descripcion,
  duracion_estimada: currentService.duracion_estimada,
  precio: currentService.precio,
});

const handleSubmit = () => {
  alert('Servicio actualizado exitosamente (funcionalidad de backend pendiente)');
  router.visit(route('services.index'));
};
</script>

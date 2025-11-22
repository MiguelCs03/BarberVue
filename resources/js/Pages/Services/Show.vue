<template>
  <Head :title="`Servicio: ${service.nombre}`" />

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

      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
            {{ service.nombre }}
          </h1>
          <p :style="{ color: 'var(--text-secondary)' }">
            Detalles del servicio
          </p>
        </div>

        <Link :href="route('services.edit', service.id)" class="btn-primary">
          Editar Servicio
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <Card>
          <h2 class="text-xl font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Información del Servicio
          </h2>

          <div class="space-y-4">
            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Nombre
              </label>
              <p class="mt-1 text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                {{ service.nombre }}
              </p>
            </div>

            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Descripción
              </label>
              <p class="mt-1" :style="{ color: 'var(--text-primary)' }">
                {{ service.descripcion }}
              </p>
            </div>
          </div>
        </Card>
      </div>

      <div class="space-y-6">
        <Card>
          <h3 class="text-lg font-bold mb-4" :style="{ color: 'var(--text-primary)' }">
            Detalles
          </h3>

          <div class="space-y-4">
            <div>
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Duración
              </label>
              <p class="mt-1 text-2xl font-bold" :style="{ color: 'var(--color-secondary)' }">
                {{ service.duracion_estimada }} min
              </p>
            </div>

            <div class="pt-4 border-t" :style="{ borderColor: 'var(--text-secondary)' }">
              <label class="text-sm font-medium" :style="{ color: 'var(--text-secondary)' }">
                Precio
              </label>
              <p class="mt-1 text-2xl font-bold" :style="{ color: 'var(--color-primary)' }">
                Bs. {{ service.precio.toFixed(2) }}
              </p>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const serviceId = window.location.pathname.split('/').filter(Boolean).pop();

const mockServices = {
  '1': { id: 1, nombre: 'Corte de cabello', descripcion: 'Corte clásico de caballero', duracion_estimada: 30, precio: 15.00 },
  '2': { id: 2, nombre: 'Afeitado', descripcion: 'Afeitado con navaja y toalla caliente', duracion_estimada: 20, precio: 10.00 },
  '3': { id: 3, nombre: 'Corte + Barba', descripcion: 'Corte y arreglo de barba', duracion_estimada: 45, precio: 25.00 },
  '4': { id: 4, nombre: 'Coloración', descripcion: 'Tinte y tratamiento', duracion_estimada: 60, precio: 40.00 },
  '5': { id: 5, nombre: 'Tratamiento capilar', descripcion: 'Mascarilla y secado profesional', duracion_estimada: 40, precio: 20.00 },
  '6': { id: 6, nombre: 'Perfilado de barba', descripcion: 'Perfilado detalle', duracion_estimada: 15, precio: 8.00 },
};

const service = mockServices[serviceId] || mockServices['1'];
</script>

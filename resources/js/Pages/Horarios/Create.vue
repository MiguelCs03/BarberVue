<template>
  <Head title="Crear Horario" />

  <AppLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Agregar Horario
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Define tu disponibilidad para un día de la semana
      </p>
    </div>

    <Card>
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Día de la Semana -->
        <div>
          <label class="block mb-2 font-medium" :style="{ color: 'var(--text-primary)' }">
            Día de la Semana <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.dia_semana"
            class="input w-full"
            required
          >
            <option value="">Selecciona un día</option>
            <option value="1">Lunes</option>
            <option value="2">Martes</option>
            <option value="3">Miércoles</option>
            <option value="4">Jueves</option>
            <option value="5">Viernes</option>
            <option value="6">Sábado</option>
            <option value="7">Domingo</option>
          </select>
          <p v-if="form.errors.dia_semana" class="text-red-500 text-sm mt-1">
            {{ form.errors.dia_semana }}
          </p>
        </div>

        <!-- Hora Inicio -->
        <div>
          <label class="block mb-2 font-medium" :style="{ color: 'var(--text-primary)' }">
            Hora de Inicio <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.hora_inicio"
            type="time"
            class="input w-full"
            required
          />
          <p v-if="form.errors.hora_inicio" class="text-red-500 text-sm mt-1">
            {{ form.errors.hora_inicio }}
          </p>
        </div>

        <!-- Hora Fin -->
        <div>
          <label class="block mb-2 font-medium" :style="{ color: 'var(--text-primary)' }">
            Hora de Fin <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.hora_fin"
            type="time"
            class="input w-full"
            required
          />
          <p v-if="form.errors.hora_fin" class="text-red-500 text-sm mt-1">
            {{ form.errors.hora_fin }}
          </p>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3 justify-end">
          <Link
            :href="route('horarios.index')"
            class="btn-secondary"
          >
            Cancelar
          </Link>
          <button
            type="submit"
            class="btn-primary"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Guardando...' : 'Guardar Horario' }}
          </button>
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
  dia_semana: '',
  hora_inicio: '',
  hora_fin: '',
});

const submit = () => {
  form.post(route('horarios.store'), {
    preserveScroll: true,
  });
};
</script>

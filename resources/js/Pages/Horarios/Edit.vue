<template>
  <Head title="Editar Horario" />

  <AppLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Editar Horario
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Modifica el horario de {{ horario.dia_semana }}
      </p>
    </div>

    <Card>
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Día de la Semana (solo lectura) -->
        <div>
          <label class="block mb-2 font-medium" :style="{ color: 'var(--text-primary)' }">
            Día de la Semana
          </label>
          <input
            :value="horario.dia_semana"
            type="text"
            class="input w-full bg-gray-100"
            disabled
          />
          <p class="text-sm mt-1" :style="{ color: 'var(--text-secondary)' }">
            No puedes cambiar el día. Si necesitas un horario diferente, crea uno nuevo.
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
            {{ form.processing ? 'Guardando...' : 'Actualizar Horario' }}
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

const props = defineProps({
  horario: Object,
});

const form = useForm({
  hora_inicio: props.horario.hora_inicio,
  hora_fin: props.horario.hora_fin,
});

const submit = () => {
  form.put(route('horarios.update', props.horario.id), {
    preserveScroll: true,
  });
};
</script>

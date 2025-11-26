<template>
  <Head title="Gestión de Horarios" />

  <AppLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Gestión de Horarios
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        {{ esAdmin ? 'Administra los horarios de todos los barberos' : 'Configura tu disponibilidad semanal y excepciones' }}
      </p>
    </div>

    <!-- Vista Administrativa (para propietarios/admins) -->
    <div v-if="esAdmin">
      <div v-for="barbero in barberos" :key="barbero.id" class="mb-8">
        <Card>
          <!-- Nombre del Barbero -->
          <div class="mb-4 pb-4 border-b" :style="{ borderColor: 'var(--border-color)' }">
            <h2 class="text-2xl font-semibold" :style="{ color: 'var(--text-primary)' }">
              {{ barbero.nombre }}
            </h2>
          </div>

          <!-- Horarios Semanales -->
          <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                Horarios Semanales
              </h3>
              <button
                @click="abrirFormularioHorario(barbero.id)"
                class="btn-primary btn-sm"
              >
                + Agregar Horario
              </button>
            </div>

            <div v-if="barbero.horarios.length > 0" class="space-y-2">
              <div
                v-for="horario in barbero.horarios"
                :key="horario.id"
                class="flex items-center justify-between p-3 rounded-lg"
                :style="{ backgroundColor: 'var(--bg-secondary)' }"
              >
                <div class="flex items-center gap-3">
                  <Badge variant="primary" class="text-sm">{{ horario.dia_nombre }}</Badge>
                  <span :style="{ color: 'var(--text-primary)' }" class="text-sm">
                    {{ horario.hora_inicio }} - {{ horario.hora_fin }}
                  </span>
                </div>
                <div class="flex gap-2">
                  <button
                    @click="editarHorario(horario, barbero.id)"
                    class="btn-secondary btn-sm"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </button>
                  <button
                    @click="eliminarHorario(horario.id)"
                    class="btn-danger btn-sm"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-4 text-sm" :style="{ color: 'var(--text-secondary)' }">
              Sin horarios configurados
            </div>
          </div>

          <!-- Excepciones -->
          <div>
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold" :style="{ color: 'var(--text-primary)' }">
                Excepciones
              </h3>
              <button
                @click="abrirFormularioExcepcion(barbero.id)"
                class="btn-primary btn-sm"
              >
                + Agregar Excepción
              </button>
            </div>

            <div v-if="barbero.excepciones.length > 0" class="space-y-2">
              <div
                v-for="excepcion in barbero.excepciones"
                :key="excepcion.id"
                class="flex items-center justify-between p-3 rounded-lg"
                :style="{ backgroundColor: 'var(--bg-secondary)' }"
              >
                <div class="flex items-center gap-3">
                  <Badge :variant="excepcion.es_disponible ? 'success' : 'danger'" class="text-sm">
                    {{ excepcion.fecha }}
                  </Badge>
                  <div>
                    <p :style="{ color: 'var(--text-primary)' }" class="text-sm">
                      {{ excepcion.es_disponible ? 'Disponible' : 'No disponible' }}
                      <span v-if="excepcion.es_disponible && excepcion.hora_inicio" class="text-xs">
                        ({{ excepcion.hora_inicio }} - {{ excepcion.hora_fin }})
                      </span>
                    </p>
                    <p v-if="excepcion.motivo" :style="{ color: 'var(--text-secondary)' }" class="text-xs">
                      {{ excepcion.motivo }}
                    </p>
                  </div>
                </div>
                <button
                  @click="eliminarExcepcion(excepcion.id)"
                  class="btn-danger btn-sm"
                >
                  <TrashIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div v-else class="text-center py-4 text-sm" :style="{ color: 'var(--text-secondary)' }">
              Sin excepciones configuradas
            </div>
          </div>
        </Card>
      </div>
    </div>

    <!-- Vista de Barbero (para barberos individuales) -->
    <div v-else>
      <!-- Horarios Regulares -->
      <Card class="mb-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold" :style="{ color: 'var(--text-primary)' }">
            Horarios Semanales
          </h2>
          <Link
            :href="route('horarios.create')"
            class="btn-primary"
          >
            + Agregar Horario
          </Link>
        </div>

        <div v-if="horarios.length > 0" class="space-y-3">
          <div
            v-for="horario in horariosOrdenados"
            :key="horario.id"
            class="flex items-center justify-between p-4 rounded-lg"
            :style="{ backgroundColor: 'var(--bg-secondary)' }"
          >
            <div class="flex items-center gap-4">
              <Badge variant="primary">{{ horario.dia_nombre }}</Badge>
              <span :style="{ color: 'var(--text-primary)' }">
                {{ horario.hora_inicio }} - {{ horario.hora_fin }}
              </span>
            </div>
            <div class="flex gap-2">
              <Link
                :href="route('horarios.edit', horario.id)"
                class="btn-secondary btn-sm"
              >
                <PencilIcon class="w-4 h-4" />
              </Link>
              <button
                @click="eliminarHorario(horario.id)"
                class="btn-danger btn-sm"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8" :style="{ color: 'var(--text-secondary)' }">
          No has configurado horarios. Agrega tu disponibilidad semanal.
        </div>
      </Card>

      <!-- Excepciones -->
      <Card>
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold" :style="{ color: 'var(--text-primary)' }">
            Excepciones de Horario
          </h2>
          <button
            @click="mostrarFormularioExcepcion = true"
            class="btn-primary"
          >
            + Agregar Excepción
          </button>
        </div>

        <!-- Formulario de Excepción -->
        <div
          v-if="mostrarFormularioExcepcion"
          class="mb-6 p-4 rounded-lg"
          :style="{ backgroundColor: 'var(--bg-secondary)' }"
        >
          <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--text-primary)' }">
            Nueva Excepción
          </h3>
          <form @submit.prevent="guardarExcepcion" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
                  Fecha
                </label>
                <input
                  v-model="formExcepcion.fecha"
                  type="date"
                  class="input w-full"
                  required
                />
              </div>

              <div>
                <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
                  Estado
                </label>
                <select
                  v-model="formExcepcion.es_disponible"
                  class="input w-full"
                  required
                >
                  <option :value="false">No disponible</option>
                  <option :value="true">Disponible con horario especial</option>
                </select>
              </div>

              <div v-if="formExcepcion.es_disponible">
                <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
                  Hora Inicio
                </label>
                <input
                  v-model="formExcepcion.hora_inicio"
                  type="time"
                  class="input w-full"
                  required
                />
              </div>

              <div v-if="formExcepcion.es_disponible">
                <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
                  Hora Fin
                </label>
                <input
                  v-model="formExcepcion.hora_fin"
                  type="time"
                  class="input w-full"
                  required
                />
              </div>

              <div class="md:col-span-2">
                <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
                  Motivo (opcional)
                </label>
                <input
                  v-model="formExcepcion.motivo"
                  type="text"
                  class="input w-full"
                  placeholder="Ej: Vacaciones, Día libre, etc."
                />
              </div>
            </div>

            <div class="flex gap-2 justify-end">
              <button
                type="button"
                @click="cancelarExcepcion"
                class="btn-secondary"
              >
                Cancelar
              </button>
              <button type="submit" class="btn-primary">
                Guardar Excepción
              </button>
            </div>
          </form>
        </div>

        <!-- Lista de Excepciones -->
        <div v-if="excepciones.length > 0" class="space-y-3">
          <div
            v-for="excepcion in excepcionesOrdenadas"
            :key="excepcion.id"
            class="flex items-center justify-between p-4 rounded-lg"
            :style="{ backgroundColor: 'var(--bg-secondary)' }"
          >
            <div class="flex items-center gap-4">
              <Badge :variant="excepcion.es_disponible ? 'success' : 'danger'">
                {{ excepcion.fecha }}
              </Badge>
              <div>
                <p :style="{ color: 'var(--text-primary)' }">
                  {{ excepcion.es_disponible ? 'Disponible' : 'No disponible' }}
                  <span v-if="excepcion.es_disponible && excepcion.hora_inicio">
                    ({{ excepcion.hora_inicio }} - {{ excepcion.hora_fin }})
                  </span>
                </p>
                <p v-if="excepcion.motivo" :style="{ color: 'var(--text-secondary)' }" class="text-sm">
                  {{ excepcion.motivo }}
                </p>
              </div>
            </div>
            <button
              @click="eliminarExcepcion(excepcion.id)"
              class="btn-danger btn-sm"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div v-else-if="!mostrarFormularioExcepcion" class="text-center py-8" :style="{ color: 'var(--text-secondary)' }">
          No hay excepciones configuradas.
        </div>
      </Card>
    </div>

    <!-- Modal para Admin: Agregar/Editar Horario -->
    <div
      v-if="modalHorario.mostrar"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="cerrarModalHorario"
    >
      <Card class="w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4" :style="{ color: 'var(--text-primary)' }">
          {{ modalHorario.editando ? 'Editar Horario' : 'Nuevo Horario' }}
        </h3>
        <form @submit.prevent="guardarHorarioAdmin" class="space-y-4">
          <div v-if="!modalHorario.editando">
            <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
              Día de la Semana
            </label>
            <select
              v-model="modalHorario.form.dia_semana"
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
          </div>

          <div>
            <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
              Hora Inicio
            </label>
            <input
              v-model="modalHorario.form.hora_inicio"
              type="time"
              class="input w-full"
              required
            />
          </div>

          <div>
            <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
              Hora Fin
            </label>
            <input
              v-model="modalHorario.form.hora_fin"
              type="time"
              class="input w-full"
              required
            />
          </div>

          <div class="flex gap-2 justify-end">
            <button
              type="button"
              @click="cerrarModalHorario"
              class="btn-secondary"
            >
              Cancelar
            </button>
            <button type="submit" class="btn-primary">
              {{ modalHorario.editando ? 'Actualizar' : 'Guardar' }}
            </button>
          </div>
        </form>
      </Card>
    </div>

    <!-- Modal para Admin: Agregar Excepción -->
    <div
      v-if="modalExcepcion.mostrar"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="cerrarModalExcepcion"
    >
      <Card class="w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4" :style="{ color: 'var(--text-primary)' }">
          Nueva Excepción
        </h3>
        <form @submit.prevent="guardarExcepcionAdmin" class="space-y-4">
          <div>
            <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
              Fecha
            </label>
            <input
              v-model="modalExcepcion.form.fecha"
              type="date"
              class="input w-full"
              required
            />
          </div>

          <div>
            <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
              Estado
            </label>
            <select
              v-model="modalExcepcion.form.es_disponible"
              class="input w-full"
              required
            >
              <option :value="false">No disponible</option>
              <option :value="true">Disponible con horario especial</option>
            </select>
          </div>

          <div v-if="modalExcepcion.form.es_disponible">
            <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
              Hora Inicio
            </label>
            <input
              v-model="modalExcepcion.form.hora_inicio"
              type="time"
              class="input w-full"
              required
            />
          </div>

          <div v-if="modalExcepcion.form.es_disponible">
            <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
              Hora Fin
            </label>
            <input
              v-model="modalExcepcion.form.hora_fin"
              type="time"
              class="input w-full"
              required
            />
          </div>

          <div>
            <label class="block mb-2" :style="{ color: 'var(--text-primary)' }">
              Motivo (opcional)
            </label>
            <input
              v-model="modalExcepcion.form.motivo"
              type="text"
              class="input w-full"
              placeholder="Ej: Vacaciones, Día libre, etc."
            />
          </div>

          <div class="flex gap-2 justify-end">
            <button
              type="button"
              @click="cerrarModalExcepcion"
              class="btn-secondary"
            >
              Cancelar
            </button>
            <button type="submit" class="btn-primary">
              Guardar
            </button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  horarios: {
    type: Array,
    default: () => []
  },
  excepciones: {
    type: Array,
    default: () => []
  },
  barberos: {
    type: Array,
    default: () => []
  },
  esAdmin: {
    type: Boolean,
    default: false
  }
});

// Estado para vista de barbero
const mostrarFormularioExcepcion = ref(false);
const formExcepcion = ref({
  fecha: '',
  es_disponible: false,
  hora_inicio: '',
  hora_fin: '',
  motivo: '',
});

// Estado para vista de admin
const modalHorario = ref({
  mostrar: false,
  editando: false,
  barberoId: null,
  horarioId: null,
  form: {
    dia_semana: '',
    hora_inicio: '',
    hora_fin: '',
  }
});

const modalExcepcion = ref({
  mostrar: false,
  barberoId: null,
  form: {
    fecha: '',
    es_disponible: false,
    hora_inicio: '',
    hora_fin: '',
    motivo: '',
  }
});

// Ordenar horarios por día de la semana
const horariosOrdenados = computed(() => {
  return [...props.horarios].sort((a, b) => parseInt(a.dia_semana) - parseInt(b.dia_semana));
});

// Ordenar excepciones por fecha (más recientes primero)
const excepcionesOrdenadas = computed(() => {
  return [...props.excepciones].sort((a, b) => new Date(b.fecha_raw) - new Date(a.fecha_raw));
});

// Funciones para barbero
const guardarExcepcion = () => {
  router.post(route('horarios.excepciones.store'), formExcepcion.value, {
    preserveScroll: true,
    onSuccess: () => {
      cancelarExcepcion();
    },
  });
};

const cancelarExcepcion = () => {
  mostrarFormularioExcepcion.value = false;
  formExcepcion.value = {
    fecha: '',
    es_disponible: false,
    hora_inicio: '',
    hora_fin: '',
    motivo: '',
  };
};

// Funciones para admin
const abrirFormularioHorario = (barberoId) => {
  modalHorario.value = {
    mostrar: true,
    editando: false,
    barberoId: barberoId,
    horarioId: null,
    form: {
      dia_semana: '',
      hora_inicio: '',
      hora_fin: '',
    }
  };
};

const editarHorario = (horario, barberoId) => {
  modalHorario.value = {
    mostrar: true,
    editando: true,
    barberoId: barberoId,
    horarioId: horario.id,
    form: {
      dia_semana: horario.dia_semana,
      hora_inicio: horario.hora_inicio,
      hora_fin: horario.hora_fin,
    }
  };
};

const guardarHorarioAdmin = () => {
  const data = {
    ...modalHorario.value.form,
    barbero_id: modalHorario.value.barberoId
  };

  if (modalHorario.value.editando) {
    router.put(route('horarios.update', modalHorario.value.horarioId), {
      hora_inicio: data.hora_inicio,
      hora_fin: data.hora_fin,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        cerrarModalHorario();
      },
    });
  } else {
    router.post(route('horarios.store'), data, {
      preserveScroll: true,
      onSuccess: () => {
        cerrarModalHorario();
      },
    });
  }
};

const cerrarModalHorario = () => {
  modalHorario.value = {
    mostrar: false,
    editando: false,
    barberoId: null,
    horarioId: null,
    form: {
      dia_semana: '',
      hora_inicio: '',
      hora_fin: '',
    }
  };
};

const abrirFormularioExcepcion = (barberoId) => {
  modalExcepcion.value = {
    mostrar: true,
    barberoId: barberoId,
    form: {
      fecha: '',
      es_disponible: false,
      hora_inicio: '',
      hora_fin: '',
      motivo: '',
    }
  };
};

const guardarExcepcionAdmin = () => {
  const data = {
    ...modalExcepcion.value.form,
    barbero_id: modalExcepcion.value.barberoId
  };

  router.post(route('horarios.excepciones.store'), data, {
    preserveScroll: true,
    onSuccess: () => {
      cerrarModalExcepcion();
    },
  });
};

const cerrarModalExcepcion = () => {
  modalExcepcion.value = {
    mostrar: false,
    barberoId: null,
    form: {
      fecha: '',
      es_disponible: false,
      hora_inicio: '',
      hora_fin: '',
      motivo: '',
    }
  };
};

// Funciones compartidas
const eliminarHorario = (id) => {
  if (confirm('¿Estás seguro de eliminar este horario?')) {
    router.delete(route('horarios.destroy', id), {
      preserveScroll: true,
    });
  }
};

const eliminarExcepcion = (id) => {
  if (confirm('¿Estás seguro de eliminar esta excepción?')) {
    router.delete(route('horarios.excepciones.destroy', id), {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Head title="Gestión de Servicios" />

  <AppLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
        Gestión de Servicios
      </h1>
      <p :style="{ color: 'var(--text-secondary)' }">
        Administra los servicios ofrecidos en la barbería
      </p>
    </div>

    <!-- Filters and Actions -->
    <Card class="mb-6">
      <div class="flex flex-col md:flex-row gap-4 justify-between">
        <!-- Search -->
        <div class="flex-1 max-w-md">
          <input
            v-model="searchQuery"
            @input="handleSearch"
            type="text"
            placeholder="Buscar servicios..."
            class="input w-full"
          />
        </div>

        <!-- Create Button -->
        <Link
          :href="route('services.create')"
          class="btn-primary whitespace-nowrap"
        >
          + Nuevo Servicio
        </Link>
      </div>
    </Card>

    <!-- Services Table -->
    <Card>
      <DataTable
        :columns="columns"
        :data="services.data"
        :actions="actions"
      >
        <template #cell-precio="{ value }">
          <span class="font-semibold" :style="{ color: 'var(--color-primary)' }">
            Bs. {{ Number(value).toFixed(2) }}
          </span>
        </template>

        <template #cell-duracion_estimada="{ value }">
          <Badge variant="info">
            {{ value }} min
          </Badge>
        </template>

        <template #cell-estado="{ value }">
          <Badge :variant="value === 'activo' ? 'success' : 'danger'">
            {{ value === 'activo' ? 'Activo' : 'Inactivo' }}
          </Badge>
        </template>
        
        <template #cell-descripcion="{ value }">
          <span :style="{ color: value && value.trim() ? 'var(--text-primary)' : 'var(--text-secondary)' }">
            {{ value && value.trim() ? value : 'N/A' }}
          </span>
        </template>

      </DataTable>

      <!-- Pagination -->
      <div v-if="services.links && services.links.length > 3" class="mt-4 flex justify-between items-center">
        <div :style="{ color: 'var(--text-secondary)' }">
          Mostrando {{ services.from }} - {{ services.to }} de {{ services.total }}
        </div>
        <div class="flex gap-2">
          <template v-for="link in services.links" :key="link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'px-3 py-1 rounded',
                link.active ? 'btn-primary' : 'btn-secondary'
              ]"
              v-html="link.label"
            />
            <span
              v-else
              :class="['px-3 py-1 rounded btn-secondary opacity-50 cursor-not-allowed']"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
    </Card>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import DataTable from '@/Components/DataTable.vue';
import { 
  EyeIcon, 
  PencilIcon, 
  TrashIcon 
} from '@heroicons/vue/24/outline';

// Props recibidos del backend
const props = defineProps({
  services: Object,
  filters: Object,
});

// Search query sincronizado con filtros del backend
const searchQuery = ref(props.filters.search || '');

// Debounce para búsqueda
let searchTimeout = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('services.index'), { search: searchQuery.value }, {
      preserveState: true,
      preserveScroll: true,
    });
  }, 300);
};

// Table columns
const columns = [
  { key: 'nombre', label: 'Servicio' },
  { key: 'descripcion', label: 'Descripción' },
  { key: 'duracion_estimada', label: 'Duración' },
  { key: 'precio', label: 'Precio' },
  { key: 'estado', label: 'Estado' },
];


const actions = [
  {
    label: 'Ver',
    icon:EyeIcon,
    handler: (service) => router.visit(route('services.show', service.id)),
    variant: 'primary',
  },
  {
    label: 'Editar',
    icon:PencilIcon,
    handler: (service) => router.visit(route('services.edit', service.id)),
    variant: 'primary',
  },
  // {
  //   label: 'Eliminar',
  //   icon:TrashIcon,
  //   handler: (service) => {
  //     if (confirm(`¿Estás seguro de eliminar "${service.nombre}"?`)) {
  //       router.delete(route('services.destroy', service.id), {
  //         preserveScroll: true,
  //       });
  //     }
  //   },
  //   variant: 'danger',
  // },
];

</script>
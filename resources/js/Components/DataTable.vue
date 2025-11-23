<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y" :style="{ borderColor: 'var(--text-secondary)' }">
      <thead>
        <tr :style="{ backgroundColor: 'var(--bg-secondary)' }">
          <th
            v-for="column in columns"
            :key="column.key"
            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
            :style="{ color: 'var(--text-secondary)' }"
          >
            {{ column.label }}
          </th>
          <th
            v-if="actions && actions.length > 0"
            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider"
            :style="{ color: 'var(--text-secondary)' }"
          >
            Acciones
          </th>
        </tr>
      </thead>
      <tbody class="divide-y" :style="{ backgroundColor: 'var(--bg-primary)', borderColor: 'var(--text-secondary)' }">
        <tr
          v-for="(item, index) in data"
          :key="index"
          class="hover:opacity-75 transition-opacity"
        >
          <td
            v-for="column in columns"
            :key="column.key"
            class="px-6 py-4 whitespace-nowrap"
            :style="{ color: 'var(--text-primary)' }"
          >
            <slot :name="`cell-${column.key}`" :item="item" :value="item[column.key]">
              {{ item[column.key] }}
            </slot>
          </td>
          <td
            v-if="actions && actions.length > 0"
            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
          >
            <div class="flex justify-end gap-2">
              <button
                v-for="action in actions"
                :key="action.label"
                @click="action.handler(item)"
                class="px-3 py-1 rounded hover:opacity-75 transition-opacity"
                :style="{ 
                  backgroundColor: action.variant === 'danger' ? '#EF4444' : 'var(--color-primary)',
                  color: 'white'
                }"
                :title="action.label"
              >
                <!-- {{ action.label }} -->
                <component v-if="action.icon" :is="action.icon" class="w-5 h-5" />
                <span v-else>{{ action.label }}</span>
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="data.length === 0">
          <td
            :colspan="columns.length + (actions && actions.length > 0 ? 1 : 0)"
            class="px-6 py-8 text-center"
            :style="{ color: 'var(--text-secondary)' }"
          >
            No hay datos disponibles
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  columns: {
    type: Array,
    required: true,
    // Format: [{ key: 'name', label: 'Nombre' }, ...]
  },
  data: {
    type: Array,
    required: true,
  },
  actions: {
    type: Array,
    default: () => [],
    // Format: [{ label: 'Ver', handler: (item) => {}, variant: 'primary' }, ...]
  },
});
</script>

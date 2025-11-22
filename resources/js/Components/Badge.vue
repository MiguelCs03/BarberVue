<template>
  <span 
    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
    :class="badgeClasses"
  >
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'secondary', 'success', 'warning', 'danger'].includes(value),
  },
});

const badgeClasses = computed(() => {
  const variants = {
    default: 'bg-gray-100 text-gray-800',
    primary: 'text-white',
    secondary: 'text-white',
    success: 'bg-green-100 text-green-800',
    warning: 'bg-yellow-100 text-yellow-800',
    danger: 'bg-red-100 text-red-800',
  };

  return variants[props.variant] || variants.default;
});
</script>

<style scoped>
.text-white[class*="primary"] {
  background-color: var(--color-primary);
}

.text-white[class*="secondary"] {
  background-color: var(--color-secondary);
}
</style>

<template>
  <div class="mb-6">
    <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--text-primary)' }">
      Horario Disponible
    </h3>
    
    <div class="flex justify-center">
      <div ref="hoursContainer" class="flex gap-2 overflow-x-auto pb-2 hide-scrollbar scroll-smooth snap-x snap-mandatory">
        <div class="flex-shrink-0 w-8"></div>
        <button
          v-for="hour in availableHours"
          :key="hour.time"
          @click="handleSelect(hour)"
          :disabled="hour.disabled"
          :title="hour.disabled ? 'Esta hora ya no está disponible' : ''"
          :class="[
            'hour-button flex-shrink-0 py-2 px-4 rounded-lg transition-all font-medium snap-center',
            hour.disabled 
              ? 'opacity-30 cursor-not-allowed bg-gray-500 text-white' 
              : modelValue === hour.time 
                ? 'btn-primary' 
                : 'btn-secondary'
          ]"
        >
          {{ hour.time }}
        </button>
        <div class="flex-shrink-0 w-8"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: null
  },
  availableHours: {
    type: Array,
    required: true,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue']);

const hoursContainer = ref(null);

const handleSelect = (hour) => {
  if (hour.disabled) return;
  
  if (props.modelValue === hour.time) {
    emit('update:modelValue', null);
    return;
  }
  
  emit('update:modelValue', hour.time);
};
</script>

<style scoped>
.hide-scrollbar {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}

.snap-x {
  scroll-snap-type: x mandatory;
}

.snap-center {
  scroll-snap-align: center;
}
.hour-button.hour-disabled {
  opacity: 0.3;
  background-color: #f3f4f6;
  color: #9ca3af;
  cursor: not-allowed;
  pointer-events: auto;
}

.hour-button.hour-disabled:hover {
  cursor: not-allowed;
}
</style>
<script setup>
import { formatDateIndonesia } from '@/lib/formatters';
import { computed } from 'vue';

defineProps({
  errorMessages: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: 'Pilih Tanggal',
  },
  density: {
    type: String,
    default: 'default',
  },
  min: {
    type: Date,
    default: null,
  },
  max: {
    type: Date,
    default: () => new Date(),
  },
});

const pickedDate = defineModel({ type: Date });
const formattedDate = computed(() => formatDateIndonesia(pickedDate.value));
</script>

<template>
  <v-text-field
    :model-value="formattedDate"
    :density
    :label
    readonly
    prepend-inner-icon="mdi-calendar"
    :error-messages="errorMessages"
  >
    <v-menu
      v-slot="{ isActive }"
      activator="parent"
      :close-on-content-click="false"
      transition="slide-y-transition"
    >
      <v-date-picker
        v-model="pickedDate"
        header="Pilih Tanggal"
        :first-day-of-week="1"
        hide-header
        :min="min"
        :max="max"
        @update:model-value="isActive.value = false"
        min-width="100%"
      />
    </v-menu>
  </v-text-field>
</template>
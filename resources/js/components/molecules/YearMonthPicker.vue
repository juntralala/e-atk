<script setup>
import { computed, ref } from 'vue';

const model = defineModel({
  default() {
    const date = new Date();
    return {
      month: date.getMonth() + 1,
      year: date.getFullYear(),
    };
  },
});

const props = defineProps({
  minYear: {
    type: Number,
    default: 2000,
  },
  maxYear: {
    type: Number,
    default: new Date().getFullYear() + 5,
  },
  label: {
    type: String,
    default: "periode"
  }
});

const emit = defineEmits(['change'])

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
const MONTHS_FULL = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const menu = ref(false);
const displayYear = ref(model.value.year);

const yearMonthString = computed(() => `${MONTHS_FULL[model.value.month - 1]} ${model.value.year}`);

function prevYear() {
  if (displayYear.value > props.minYear) displayYear.value--;
}

function nextYear() {
  if (displayYear.value < props.maxYear) displayYear.value++;
}

function selectMonth(monthIndex) {
  model.value = { month: monthIndex + 1, year: displayYear.value };
  menu.value = false;
  emit('change');
}

function isSelected(monthIndex) {
  return model.value.month === monthIndex + 1 && model.value.year === displayYear.value;
}

function onMenuOpen() {
  displayYear.value = model.value.year;
}
</script>

<template>
  <v-menu
    v-model="menu"
    :close-on-content-click="false"
    transition="scale-transition"
    offset-y
    @update:model-value="(val) => val && onMenuOpen()"
  >
    <template #activator="{ props: menuProps }">
      <v-text-field
        :model-value="yearMonthString"
        v-bind="menuProps"
        :label="label"
        variant="outlined"
        density="compact"
        hide-details
        readonly
        style="min-width: 200px"
        append-inner-icon="mdi-calendar"
      />
    </template>

    <v-card width="280">
      <!-- Header navigasi tahun -->
      <v-card-title class="d-flex align-center justify-space-between pa-3">
        <v-btn
          icon="mdi-chevron-left"
          variant="text"
          density="comfortable"
          :disabled="displayYear <= minYear"
          @click="prevYear"
        />
        <span class="text-body-1 font-weight-bold">{{ displayYear }}</span>
        <v-btn
          icon="mdi-chevron-right"
          variant="text"
          density="comfortable"
          :disabled="displayYear >= maxYear"
          @click="nextYear"
        />
      </v-card-title>

      <v-divider />

      <!-- Grid bulan -->
      <v-card-text class="pa-3">
        <v-row no-gutters>
          <v-col
            v-for="(month, index) in MONTHS"
            :key="index"
            cols="3"
            class="pa-1"
          >
            <v-btn
              :variant="isSelected(index) ? 'flat' : 'text'"
              :color="isSelected(index) ? 'primary' : undefined"
              density="comfortable"
              block
              rounded="lg"
              @click="selectMonth(index)"
            >
              {{ month }}
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-menu>
</template>

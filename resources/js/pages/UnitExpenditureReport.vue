<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import DatePicker from '@/components/molecules/DatePicker.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  unitExpenditures: {
    type: Object,
    required: true,
  },
  total: {
    type: Number,
    default: 0,
  },
  filters: {
    type: Object,
    default: () => ({
      start: null,
      end: null,
    }),
  },
});

// Ambil query params dari URL
const urlParams = computed(() => new URLSearchParams(window.location.search));

const startDate = ref(
  urlParams.value.get('start')
    ? new Date(urlParams.value.get('start'))
    : new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
);

const endDate = ref(
  urlParams.value.get('end')
    ? new Date(urlParams.value.get('end'))
    : new Date()
);

const applyFilter = () => {
  router.get(
    route('expenditures.units.exports.view'),
    {
      start: startDate.value.toISOString(),
      end: endDate.value.toISOString(),
      page: 1,
    },
    {
      preserveState: false,
      preserveScroll: false,
    },
  );
};

const downloadSpreadsheet = () => {
  window.location.href = route('expenditures.units.exports.xlsx', {
    start: startDate.value.toISOString(),
    end: endDate.value.toISOString(),
  });
};

const handlePageChange = (page) => {
  router.get(
    route('units.expenditures.exports.view'),
    {
      start: startDate.value.toISOString(),
      end: endDate.value.toISOString(),
      page,
    },
    {
      preserveState: false,
      preserveScroll: true,
    },
  );
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value);
};
</script>

<template>
  <Head title="Laporan Pengeluaran Unit"></Head>
  <v-container>
    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Laporan"
          second-part-title="Pengeluaran Unit"
        />
      </v-col>
    </v-row>

    <!-- Filter Section -->
    <v-row>
      <v-col
        cols="12"
        md="4"
      >
        <DatePicker
          v-model="startDate"
          label="Tanggal Mulai"
          density="comfortable"
          :max="endDate"
        />
      </v-col>
      <v-col
        cols="12"
        md="4"
      >
        <DatePicker
          v-model="endDate"
          label="Tanggal Akhir"
          density="comfortable"
          :min="startDate"
        />
      </v-col>
      <v-col
        cols="12"
        md="4"
        class="flex items-center gap-2"
      >
        <v-btn
          variant="tonal"
          color="blue-darken-2"
          @click="applyFilter"
        >
          <v-icon
            icon="mdi-filter"
            class="mr-2"
          />
          Filter
        </v-btn>
        <v-btn
          variant="tonal"
          color="blue-darken-2"
          @click="downloadSpreadsheet"
        >
          <v-icon
            icon="mdi-download"
            class="mr-2"
          />
          Unduh
        </v-btn>
      </v-col>
    </v-row>

    <!-- Table Section -->
    <v-row class="mt-4">
      <v-col>
        <v-data-table
          :headers="[
            { title: 'No', key: 'no', width: '10%' },
            { title: 'Nama Unit', key: 'name' },
            { title: 'Pengeluaran', key: 'expenditure', width: '25%' },
          ]"
          :items="unitExpenditures.data"
          :items-per-page="unitExpenditures.per_page"
          hide-default-footer
        >
          <template #headers="{ headers }">
            <tr class="bg-blue-darken-2">
              <th
                v-for="i in headers.at(0).length"
                :key="i"
              >
                {{ headers.at(0).at(i - 1).title }}
              </th>
            </tr>
          </template>
          <template #item.no="{ index }">
            {{ (unitExpenditures.current_page - 1) * unitExpenditures.per_page + index + 1 }}
          </template>
          <template #item.expenditure="{ item }">
            {{ formatCurrency(item.expenditure) }}
          </template>
          <template #bottom>
            <tr class="bg-blue-lighten-5 font-weight-bold">
              <td
                colspan="2"
                class="pa-4 text-right"
              >
                Total:
              </td>
              <td class="pa-4">{{ formatCurrency(total) }}</td>
            </tr>
          </template>
        </v-data-table>

        <!-- Pagination -->
        <v-row class="mt-4">
          <v-col class="flex justify-center">
            <v-pagination
              v-if="unitExpenditures.last_page > 1"
              :length="unitExpenditures.last_page"
              :model-value="unitExpenditures.current_page"
              @update:model-value="handlePageChange"
              color="blue-darken-2"
              :total-visible="7"
            ></v-pagination>
          </v-col>
        </v-row>

        <!-- Empty State -->
        <v-row v-if="!unitExpenditures.data || unitExpenditures.data.length === 0">
          <v-col>
            <v-card>
              <v-card-text class="text-grey pa-8 text-center">
                Tidak ada data pengeluaran unit pada periode ini
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>
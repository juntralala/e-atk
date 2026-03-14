<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import DatePicker from '@/components/molecules/DatePicker.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  itemExpenditures: {
    type: Object,
    required: true,
  },
  units: {
    type: Array,
    default: () => [],
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
      unit: null,
    }),
  },
});

const urlParams = computed(() => new URLSearchParams(window.location.search));

const startDate = ref(
  urlParams.value.get('start')
    ? new Date(urlParams.value.get('start'))
    : new Date(Date.now() - 30 * 24 * 60 * 60 * 1000),
);

const endDate = ref(
  urlParams.value.get('end') ? new Date(urlParams.value.get('end')) : new Date(),
);

const selectedUnit = ref(urlParams.value.get('unit') || null);

// Unit options: null = Semua, plus units from props
const unitOptions = computed(() => [
  { title: 'Semua Unit', value: null },
  ...props.units.map((u) => ({
    title: typeof u === 'object' ? u.name : u,
    value: typeof u === 'object' ? u.id ?? u.name : u,
  })),
]);

const applyFilter = () => {
  router.get(
    route('items.expenditures.exports.view'),
    {
      start: startDate.value.toISOString(),
      end: endDate.value.toISOString(),
      unit: selectedUnit.value,
      page: 1,
    },
    {
      preserveState: false,
      preserveScroll: false,
    },
  );
};

const downloadSpreadsheet = () => {
  const params = new URLSearchParams({
    start: startDate.value.toISOString(),
    end: endDate.value.toISOString(),
  });
  if (selectedUnit.value) params.set('unit', selectedUnit.value);
  window.location.href = route('items.expenditures.exports.xlsx') + '?' + params.toString();
};

const handlePageChange = (page) => {
  router.get(
    route('items.expenditures.exports.view'),
    {
      start: startDate.value.toISOString(),
      end: endDate.value.toISOString(),
      unit: selectedUnit.value,
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

const formatNumber = (value) => {
  return new Intl.NumberFormat('id-ID').format(value);
};
</script>

<template>
  <Head title="Laporan Pengeluaran Barang"></Head>
  <v-container>
    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Laporan"
          second-part-title="Pengeluaran Barang"
        />
      </v-col>
    </v-row>

    <!-- Filter Section -->
    <v-row class="items-start">
      <v-col
        cols="12"
        md="3"
      >
        <DatePicker
          v-model="startDate"
          label="Tanggal Mulai"
          density="compact"
          :max="endDate"
        />
      </v-col>
      <v-col
        cols="12"
        md="3"
      >
        <DatePicker
          v-model="endDate"
          label="Tanggal Akhir"
          density="compact"
          :min="startDate"
        />
      </v-col>
      <v-col
        cols="12"
        md="3"
      >
        <v-select
          v-model="selectedUnit"
          :items="unitOptions"
          item-title="title"
          item-value="value"
          label="Unit"
          density="compact"
          variant="outlined"
          clearable
          clear-icon="mdi-close-circle"
          prepend-inner-icon="mdi-office-building-outline"
        />
      </v-col>
      <v-col
        cols="12"
        md="3"
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
            { title: 'No', key: 'no', width: '5%' },
            { title: 'Nama Barang', key: 'name', width: '20%' },
            { title: 'Nama Spesifikasi', key: 'spesification_name', width: '20%' },
            { title: 'Jumlah', key: 'quantity_total', width: '10%' },
            { title: 'Satuan', key: 'unit', width: '12%' },
            { title: 'Harga Satuan', key: 'avg_price', width: '15%' },
            { title: 'Pengeluaran', key: 'expenditure', width: '18%' },
          ]"
          :items="itemExpenditures.data"
          :items-per-page="itemExpenditures.per_page"
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
            {{ (itemExpenditures.current_page - 1) * itemExpenditures.per_page + index + 1 }}
          </template>
          <template #item.unit="{ item }">
            {{ item.unit?.name || '-' }}
          </template>
          <template #item.quantity_total="{ item }">
            {{ formatNumber(item.quantity_total || 0) }}
          </template>
          <template #item.avg_price="{ item }">
            {{ formatCurrency(item.avg_price || 0) }}
          </template>
          <template #item.expenditure="{ item }">
            {{ formatCurrency(item.expenditure || 0) }}
          </template>
          <template #bottom>
            <tr class="bg-blue-lighten-5 font-weight-bold">
              <td
                colspan="6"
                class="pa-4 text-right"
              >
                Total Keseluruhan:
              </td>
              <td class="pa-4">{{ formatCurrency(total) }}</td>
            </tr>
          </template>
        </v-data-table>

        <!-- Pagination -->
        <v-row class="mt-4">
          <v-col class="flex justify-center">
            <v-pagination
              v-if="itemExpenditures.last_page > 1"
              :length="itemExpenditures.last_page"
              :model-value="itemExpenditures.current_page"
              @update:model-value="handlePageChange"
              color="blue-darken-2"
              :total-visible="7"
            ></v-pagination>
          </v-col>
        </v-row>

        <!-- Empty State -->
        <v-row v-if="!itemExpenditures.data || itemExpenditures.data.length === 0">
          <v-col>
            <v-card>
              <v-card-text class="text-grey pa-8 text-center">
                Tidak ada data pengeluaran barang pada periode ini
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>
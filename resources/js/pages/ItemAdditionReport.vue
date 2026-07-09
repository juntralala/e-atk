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
  itemAdditions: {
    type: Object,
    required: true,
  },
  priceTotal: {
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
    : new Date(Date.now() - 30 * 24 * 60 * 60 * 1000),
);

const endDate = ref(urlParams.value.get('end') ? new Date(urlParams.value.get('end')) : new Date());

const applyFilter = () => {
  router.get(
    route('items.additions.exports.view'),
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
  window.location.href = route('items.additions.exports.xlsx', {
    start: startDate.value.toISOString(),
    end: endDate.value.toISOString(),
  });
};

const handlePageChange = (page) => {
  router.get(
    route('items.additions.exports.view'),
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

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};

// Flatten data untuk table
const tableData = props.itemAdditions.data.flatMap((addition, additionIndex) => {
  return addition.item_addition_details.map((detail, detailIndex) => {
    const globalIndex =
      props.itemAdditions.data.slice(0, additionIndex).reduce((sum, a) => sum + a.item_addition_details.length, 0) +
      detailIndex;

    return {
      no: (props.itemAdditions.current_page - 1) * props.itemAdditions.per_page + globalIndex + 1,
      itemName: detail.item.name,
      specification: detail.item.specification_name,
      officer: addition.user.name,
      date: addition.addition_date,
      quantity: detail.quantity,
      unit: detail.item.unit.name,
      price: detail.price,
      subtotal: detail.quantity * detail.price,
    };
  });
});
</script>

<template>
  <Head title="Laporan Penambahan Barang"></Head>
  <v-container>
    <v-row>
      <v-col>
        <PageTitleHighlightPart first-part-title="Laporan" second-part-title="Penambahan Barang" />
      </v-col>
    </v-row>

    <!-- Filter Section -->
    <v-row class="items-start">
      <v-col cols="12" md="4">
        <DatePicker v-model="startDate" label="Tanggal Mulai" density="compact" :max="endDate" />
      </v-col>
      <v-col cols="12" md="4">
        <DatePicker v-model="endDate" label="Tanggal Akhir" density="compact" :min="startDate" />
      </v-col>
      <v-col cols="12" md="4" class="flex items-center gap-2">
        <v-btn variant="tonal" color="blue-darken-2" @click="applyFilter">
          <v-icon icon="mdi-filter" class="mr-2" />
          Filter
        </v-btn>
        <v-btn variant="tonal" color="blue-darken-2" @click="downloadSpreadsheet">
          <v-icon icon="mdi-download" class="mr-2" />
          Unduh
        </v-btn>
      </v-col>
    </v-row>

    <!-- Table Section -->
    <v-row class="mt-4">
      <v-col>
        <v-data-table
          :headers="[
            { title: 'No', key: 'no', width: '6%' },
            { title: 'Nama Barang', key: 'itemName' },
            { title: 'Nama Spesifikasi', key: 'specification' },
            { title: 'Petugas', key: 'officer', width: '15%' },
            { title: 'Tanggal', key: 'date', width: '12%' },
            { title: 'Jumlah', key: 'quantity', width: '8%' },
            { title: 'Ukuran Satuan', key: 'unit', width: '10%' },
            { title: 'Harga Satuan', key: 'price', width: '12%' },
            { title: 'Subtotal', key: 'subtotal', width: '12%' },
          ]"
          :items="tableData"
          :items-per-page="itemAdditions.per_page"
          hide-default-footer>
          <template #headers="{ headers }">
            <tr class="bg-blue-darken-2">
              <th v-for="i in headers.at(0).length" :key="i">
                {{ headers.at(0).at(i - 1).title }}
              </th>
            </tr>
          </template>
          <template #item.quantity="{ item }">
            {{ item.quantity.toLocaleString('id-ID') }}
          </template>
          <template #item.price="{ item }">
            {{ formatCurrency(item.price) }}
          </template>
          <template #item.subtotal="{ item }">
            {{ formatCurrency(item.subtotal) }}
          </template>
          <template #item.date="{ item }">
            {{ formatDate(item.date) }}
          </template>
          <template #bottom>
            <tr class="bg-blue-lighten-5 font-weight-bold">
              <td colspan="7" class="pa-4 text-right">Total :</td>
              <td class="pa-4">{{ formatCurrency(priceTotal) }}</td>
            </tr>
          </template>
        </v-data-table>

        <!-- Pagination -->
        <v-row class="mt-4">
          <v-col class="flex justify-center">
            <v-pagination
              v-if="itemAdditions.last_page > 1"
              :length="itemAdditions.last_page"
              :model-value="itemAdditions.current_page"
              @update:model-value="handlePageChange"
              color="blue-darken-2"
              :total-visible="7"></v-pagination>
          </v-col>
        </v-row>

        <!-- Empty State -->
        <v-row v-if="!tableData || tableData.length === 0">
          <v-col>
            <v-card>
              <v-card-text class="text-grey pa-8 text-center">
                Tidak ada data penambahan barang pada periode ini
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

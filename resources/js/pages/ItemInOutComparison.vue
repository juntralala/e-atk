<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import YearMonthPicker from '@/components/molecules/YearMonthPicker.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

defineProps({
  comparisons: {
    type: Array,
    default() {
      return [];
    },
  },
});

function parseYearMonth(dateStr) {
  if (!dateStr) return null;
  const date = new Date(dateStr);
  if (isNaN(date)) return null;
  return { month: date.getMonth() + 1, year: date.getFullYear() };
}

const params = new URLSearchParams(window.location.search);
const now = new Date();

const yearMonthStart = ref(parseYearMonth(params.get('start')) ?? { month: now.getMonth() + 1, year: now.getFullYear() });
const yearMonthEnd = ref(parseYearMonth(params.get('end')) ?? { month: now.getMonth() + 1, year: now.getFullYear() });

function formatYearMonthStart(yearMonthStart) {
  const { year, month } = yearMonthStart.value;
  const mm = String(month).padStart(2, '0');
  return `${year}-${mm}-01 00:00:00+08:00`;
}

function formatYearMonthEnd(yearMonthEnd) {
  const endYear = yearMonthEnd.value.year;
  const endMonth = String(yearMonthEnd.value.month).padStart(2, '0');
  return `${endYear}-${endMonth}-${new Date(endYear, endMonth, 0).getDate()} 23:59:59+08:00`;
}

function handleYearMonthChange() {
  const start = formatYearMonthStart(yearMonthStart);
  const end = formatYearMonthEnd(yearMonthEnd);

  router.visit(route('items.inout-comparisons', { start, end }), {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
}

function handleDownloadSpreadSheet() {
  window.location.href = route('items.inout-comparisons.xlsx', {
    start: formatYearMonthStart(yearMonthStart),
    end: formatYearMonthEnd(yearMonthEnd),
  });
}
</script>

<template>
  <Head title="Laporan Perbandingan Keluar Masuk Barang" />
  <v-container>
    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Laporan Barang"
          second-part-title="Keluar vs Masuk"
        />
      </v-col>
    </v-row>
    <v-row>
      <v-col
        cols="12"
        md=""
      >
        <YearMonthPicker
          label="Awal"
          v-model="yearMonthStart"
          :max-year="new Date().getFullYear()"
          @change="handleYearMonthChange"
        />
      </v-col>
      <v-col
        cols="12"
        md=""
      >
        <YearMonthPicker
          label="Akhir"
          v-model="yearMonthEnd"
          :max-year="new Date().getFullYear()"
          @change="handleYearMonthChange"
        />
      </v-col>
      <v-col class="flex justify-end">
        <v-btn
          @click="handleDownloadSpreadSheet"
          color="blue"
          variant="tonal"
          >Spreadsheet</v-btn
        >
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-table>
          <thead class="bg-blue-darken-2">
            <tr>
              <td>No</td>
              <td>Nama Barang</td>
              <td>Bulan</td>
              <td>Satuan</td>
              <td>Masuk</td>
              <td>Keluar</td>
              <td>Selisih</td>
            </tr>
          </thead>
          <tbody>
            <template v-if="comparisons != null && comparisons.length > 0">
              <tr
                v-for="(comparison, index) in comparisons"
                :key="comparison.name"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ comparison.item_name }}</td>
                <td>{{ comparison.month }}</td>
                <td>{{ comparison.unit }}</td>
                <td>{{ comparison.inbound_quantity }}</td>
                <td>{{ comparison.outbound_quantity }}</td>
                <td>{{ comparison.difference }}</td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="100">
                <p class="text-center">Data Tidak Tersedia</p>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-col>
    </v-row>
  </v-container>
</template>

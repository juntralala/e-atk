<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import DatePicker from '@/components/molecules/DatePicker.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { formatDateIndonesia } from '@/lib/formatters';
import { router } from '@inertiajs/vue3';
import { onMounted, reactive, watch } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

defineProps({
  stockAdjustments: {
    type: Object,
    default: () => ({}),
  },
});

const rangeDate = reactive({
  start: new Date(new Date().setMonth(new Date().getMonth() - 1)), // default start 1 bulan yang lalu
  end: new Date(),
});

watch(
  rangeDate,
  function (newRangeDate) {
    router.reload({
      data: {
        start: newRangeDate.start,
        end: newRangeDate.end,
      },
      replace: true,
    });
  },
  { deep: false },
);

onMounted(function () {
  const params = new URLSearchParams(location.search);
  const paramStart = params.get('start');
  const paramEnd = params.get('end');

  if (paramStart) {
    const start = new Date(paramStart);
    if (!isNaN(start.getTime())) {
      rangeDate.start = start;
    }
  }

  if (paramEnd) {
    const end = new Date(paramEnd);
    if (!isNaN(end.getTime())) {
      rangeDate.end = end;
    }
  }
});
</script>

<template>
  <v-container fluid>
    <v-row>
      <v-col>
        <PageTitleHighlightPart first-part-title="Laporan" second-part-title="Penyesuaian Stok" />
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12" md="5" xl="3">
        <DatePicker
          v-model="rangeDate.start"
          label="Tanggal Mulai"
          variant="outlined"
          density="compact"
          :max="rangeDate.max" />
      </v-col>
      <v-col cols="12" md="5" xl="3">
        <DatePicker
          v-model="rangeDate.end"
          label="Tanggal Akhir"
          variant="outlined"
          density="compact"
          :min="rangeDate.start"
          :max="new Date()" />
      </v-col>
      <v-col cols="12" md="2" class="md:pt-4!">
        <v-btn variant="tonal" color="blue" prepend-icon="mdi-download" text="SpreadSheet" :href="route('stock.adjustments.exports.xlsx', rangeDate)"/>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-table>
          <thead class="bg-blue-darken-2 text-center">
            <tr>
              <td>No</td>
              <td>Tanggal</td>
              <td>Ref. Opname</td>
              <td colspan="5">Penyesuaian Barang</td>
              <td>Dibuat Oleh</td>
              <td>Catatan</td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td class="bg-blue-darken-1">Nama Barang</td>
              <td class="bg-blue-darken-1">Penyesuaian</td>
              <td class="bg-blue-darken-1">Stok Lama</td>
              <td class="bg-blue-darken-1">Stok Baru</td>
              <td class="bg-blue-darken-1">Satuan</td>
              <td></td>
              <td></td>
            </tr>
          </thead>
          <tbody>
            <template v-if="stockAdjustments.last_page <= 0">
              <tr>
                <td colspan="100" class="text-center">Belum ada penyesuaian stok pada periode ini</td>
              </tr>
            </template>
            <template v-else>
              <template v-for="(stockAdjustment, i) in stockAdjustments.data" :key="stockAdjustment.id">
                <tr>
                  <td :rowspan="stockAdjustment.details.length">{{ stockAdjustments.from + i }}</td>
                  <td :rowspan="stockAdjustment.details.length">
                    {{ formatDateIndonesia(stockAdjustment.created_at) }}
                  </td>
                  <td :rowspan="stockAdjustment.details.length">{{ '-' }}</td>
                  <td>{{ stockAdjustment.details[0].item.name }}</td>
                  <td>{{ stockAdjustment.details[0].old_stock - stockAdjustment.details[0].new_stock }}</td>
                  <td>{{ stockAdjustment.details[0].old_stock }}</td>
                  <td>{{ stockAdjustment.details[0].new_stock }}</td>
                  <td>{{ stockAdjustment.details[0].item.unit.name }}</td>
                  <td :rowspan="stockAdjustment.details.length">{{ stockAdjustment.user.name }}</td>
                  <td :rowspan="stockAdjustment.details.length">{{ stockAdjustment.notes || '-' }}</td>
                </tr>
                <template v-if="stockAdjustment.details.length > 1">
                  <tr
                    v-for="itemAdjustment in stockAdjustment.details.slice(1, stockAdjustment.details.length)"
                    :key="itemAdjustment.id">
                    <td>{{ itemAdjustment.item.name }}</td>
                    <td>{{ itemAdjustment.old_stock - itemAdjustment.new_stock }}</td>
                    <td>{{ itemAdjustment.item.unit.name }}</td>
                  </tr>
                </template>
              </template>
            </template>
          </tbody>
        </v-table>
        <v-pagination v-if="stockAdjustments.last_page > 1" :length="stockAdjustments.last_page" />
      </v-col>
    </v-row>
  </v-container>
</template>

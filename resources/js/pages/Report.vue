<script setup>
import DatePicker from '@/components/molecules/DatePicker.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { computed, ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const today = new Date();

// State untuk setiap dialog
const additions = ref({
  startDate: null,
  endDate: null,
});

const requests = ref({
  startDate: null,
  endDate: null,
});

const expenditures = ref({
  startDate: null,
  endDate: null,
});

const unitExpenditures = ref({
  startDate: null,
  endDate: null,
});

const additionsUrl = computed(() => {
  const params = new URLSearchParams();
  if (additions.value.startDate) params.append('start', additions.value.startDate.toISOString());
  if (additions.value.endDate) params.append('end', additions.value.endDate.toISOString());
  return route('items.additions.exports.xlsx') + (params.toString() ? `?${params.toString()}` : '');
});

const requestsUrl = computed(() => {
  const params = new URLSearchParams();
  if (requests.value.startDate) params.append('start', requests.value.startDate.toISOString());
  if (requests.value.endDate) params.append('end', requests.value.endDate.toISOString());
  return route('items.requests.exports.xlsx') + (params.toString() ? `?${params.toString()}` : '');
});

const expendituresUrl = computed(() => {
  const params = new URLSearchParams();
  if (expenditures.value.startDate) params.append('start', expenditures.value.startDate.toISOString());
  if (expenditures.value.endDate) params.append('end', expenditures.value.endDate.toISOString());
  return route('items.expenditures.exports.xlsx') + (params.toString() ? `?${params.toString()}` : '');
});

const unitExpendituresUrl = computed(() => {
  const params = new URLSearchParams();
  if (unitExpenditures.value.startDate) params.append('start', unitExpenditures.value.startDate.toISOString());
  if (unitExpenditures.value.endDate) params.append('end', unitExpenditures.value.endDate.toISOString());
  return route('expenditures.units.exports.xlsx') + (params.toString() ? `?${params.toString()}` : '');
});
</script>

<template>
  <v-container>
    <v-row>
      <v-col>
        <PageTitleHighlightPart first-part-title="Halaman" second-part-title="Laporan" />
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12" sm="6" lg="4">
        <v-card title="Report Stok">
          <v-card-actions>
            <v-btn :href="route('items.exports.xlsx')">
              <template #prepend>
                <v-icon icon="mdi-microsoft-excel" size="30" />
              </template>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="4">
        <v-card title="Penambahan Barang">
          <v-card-actions>
            <v-btn :href="route('items.additions.exports.xlsx')">
              <template #prepend>
                <v-icon icon="mdi-microsoft-excel" size="30" />
              </template>
            </v-btn>
          </v-card-actions>
          <v-dialog max-width="400" activator="parent">
            <template #default="{ isActive }">
              <v-card>
                <v-card-title>Filter Penambahan Barang</v-card-title>
                <v-card-text>
                  <DatePicker label="Tanggal Mulai" :max="today" v-model="additions.startDate" />
                  <DatePicker label="Tanggal Akhir" :max="today" v-model="additions.endDate" />
                </v-card-text>
                <v-card-actions>
                  <v-btn @click="isActive.value = false">Tutup</v-btn>
                  <v-btn :href="additionsUrl" prepend-icon="mdi-download">Spreadsheet</v-btn>
                </v-card-actions>
              </v-card>
            </template>
          </v-dialog>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="4">
        <v-card title="Barang Keluar">
          <v-card-actions>
            <v-btn :href="route('items.requests.exports.xlsx')">
              <template #prepend>
                <v-icon icon="mdi-microsoft-excel" size="30" />
              </template>
            </v-btn>
          </v-card-actions>
          <v-dialog v-slot="{ isActive }" max-width="400" activator="parent">
            <v-card>
              <v-card-title>Filter Barang Keluar</v-card-title>
              <v-card-text>
                <DatePicker label="Tanggal Mulai" :max="today" v-model="requests.startDate" />
                <DatePicker label="Tanggal Akhir" :max="today" v-model="requests.endDate" />
              </v-card-text>
              <v-card-actions>
                <v-btn @click="isActive.value = false">Tutup</v-btn>
                <v-btn :href="requestsUrl" prepend-icon="mdi-download">Spreadsheet</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="4">
        <v-card title="Pengeluaran Per Barang">
          <v-card-actions>
            <v-btn :href="route('items.expenditures.exports.xlsx')">
              <template #prepend>
                <v-icon icon="mdi-microsoft-excel" size="30" />
              </template>
            </v-btn>
          </v-card-actions>
          <v-dialog v-slot="{ isActive }" max-width="400" activator="parent">
            <v-card>
              <v-card-title>Filter Pengeluaran Per Barang</v-card-title>
              <v-card-text>
                <DatePicker label="Tanggal Mulai" :max="today" v-model="expenditures.startDate" />
                <DatePicker label="Tanggal Akhir" :max="today" v-model="expenditures.endDate" />
              </v-card-text>
              <v-card-actions>
                <v-btn @click="isActive.value = false">Tutup</v-btn>
                <v-btn :href="expendituresUrl" prepend-icon="mdi-download">Spreadsheet</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" lg="4">
        <v-card title="Pengeluaran Unit">
          <v-card-actions>
            <v-btn :href="route('expenditures.units.exports.xlsx')">
              <template #prepend>
                <v-icon icon="mdi-microsoft-excel" size="30" />
              </template>
            </v-btn>
          </v-card-actions>
          <v-dialog v-slot="{ isActive }" max-width="400" activator="parent">
            <v-card>
              <v-card-title>Filter Pengeluaran Unit</v-card-title>
              <v-card-text>
                <DatePicker label="Tanggal Mulai" :max="today" v-model="expenditures.startDate" />
                <DatePicker label="Tanggal Akhir" :max="today" v-model="expenditures.endDate" />
              </v-card-text>
              <v-card-actions>
                <v-btn @click="isActive.value = false">Tutup</v-btn>
                <v-btn :href="expendituresUrl" prepend-icon="mdi-download">Spreadsheet</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, watch, onBeforeMount } from 'vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import DateTimePickerInput from '@/components/molecules/DateTimePickerInput.vue';
import axios from 'axios';
import { debounce } from 'lodash';
import { formatDateIndonesia, formatRp } from '@/lib/formatters';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  statistics: Object,
  recent_transactions: Array,
  low_stock_items: Array,
  top_items: Array,
  monthly_trend: Object,
  top_recipients: Array,
  date_range: Object,
});

// Expenditure Per Item table state
const itemExpenditureData = ref([]);
const itemExpenditurePage = ref(1);
const itemExpenditurePerPage = ref(10);
const itemExpenditureLastPage = ref(1);
const itemExpenditureTotal = ref(0);
const itemExpenditureLoading = ref(false);
const searchItemOutbound = ref('');

// Date filter for item expenditure table
const itemExpenditureStartDate = ref(props.date_range.start_date);
const itemExpenditureEndDate = ref(props.date_range.end_date);

// Expenditure Per SKU table state
const expenditureData = ref([]);
const expenditurePage = ref(1);
const expenditurePerPage = ref(10);
const expenditureLastPage = ref(1);
const expenditureTotal = ref(0);
const expenditureLoading = ref(false);
const searchOutbound = ref('');

// Date filter for expenditure table
const expenditureStartDate = ref(props.date_range.start_date);
const expenditureEndDate = ref(props.date_range.end_date);

// Fetch Item Expenditure Data
const fetchItemExpenditureData = async () => {
  itemExpenditureLoading.value = true;
  try {
    const response = await axios.get(route('expenditures.items'), {
      params: {
        start: itemExpenditureStartDate.value,
        end: itemExpenditureEndDate.value,
        page: itemExpenditurePage.value,
        search: searchItemOutbound.value || undefined,
      }
    });

    itemExpenditureData.value = response.data.data;
    itemExpenditurePage.value = response.data.currentPage;
    itemExpenditurePerPage.value = response.data.perPage;
    itemExpenditureLastPage.value = response.data.lastPage;
    itemExpenditureTotal.value = response.data.total;
  } catch (error) {
    console.error('Error fetching item expenditure data:', error);
  } finally {
    itemExpenditureLoading.value = false;
  }
};

const fetchExpenditureData = async () => {
  expenditureLoading.value = true;
  try {
    const response = await axios.get(route('expenditures.skus'), {
      params: {
        start: expenditureStartDate.value,
        end: expenditureEndDate.value,
        page: expenditurePage.value,
        search: searchOutbound.value || undefined,
      }
    });

    expenditureData.value = response.data.data;
    expenditurePage.value = response.data.currentPage;
    expenditurePerPage.value = response.data.perPage;
    expenditureLastPage.value = response.data.lastPage;
    expenditureTotal.value = response.data.total;
  } catch (error) {
    console.error('Error fetching expenditure data:', error);
  } finally {
    expenditureLoading.value = false;
  }
};

// Debounced search functions
const debouncedItemSearch = debounce(() => {
  itemExpenditurePage.value = 1;
  fetchItemExpenditureData();
}, 500);

const debouncedSearch = debounce(() => {
  expenditurePage.value = 1;
  fetchExpenditureData();
}, 500);

// Debounced date filter functions
const debouncedItemDateFilter = debounce(() => {
  itemExpenditurePage.value = 1;
  fetchItemExpenditureData();
}, 800);

const debouncedDateFilter = debounce(() => {
  expenditurePage.value = 1;
  fetchExpenditureData();
}, 800);

// Watch for item expenditure changes
watch(itemExpenditurePage, () => {
  fetchItemExpenditureData();
});

watch(searchItemOutbound, () => {
  debouncedItemSearch();
});

watch([itemExpenditureStartDate, itemExpenditureEndDate], () => {
  debouncedItemDateFilter();
});

// Watch for SKU expenditure changes
watch(expenditurePage, () => {
  fetchExpenditureData();
});

watch(searchOutbound, () => {
  debouncedSearch();
});

watch([expenditureStartDate, expenditureEndDate], () => {
  debouncedDateFilter();
});

// Calculate total values
const totalItemExpenditureValue = computed(() => {
  return itemExpenditureData.value.reduce((sum, item) => sum + item.expenditure, 0);
});

const totalExpenditureValue = computed(() => {
  return expenditureData.value.reduce((sum, item) => sum + item.expenditure, 0);
});

onBeforeMount(() => {
  fetchItemExpenditureData();
  fetchExpenditureData();
});
</script>

<template>
  <v-container fluid>
    <!-- Page Title -->
    <v-row>
      <v-col>
        <h1 class="text-h4 font-weight-bold mb-2">
          Dashboard
        </h1>
      </v-col>
    </v-row>

    <!-- Statistics Cards -->
    <v-row class="mt-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Jenis Barang</p>
              <h3 class="text-h4 font-weight-bold">{{ statistics.total_items }}</h3>
            </div>
            <v-icon size="48" color="blue">mdi-package-variant</v-icon>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Total SKU</p>
              <h3 class="text-h4 font-weight-bold">{{ statistics.total_skus }}</h3>
            </div>
            <v-icon size="48" color="blue-darken-1">mdi-barcode</v-icon>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Stok Menipis</p>
              <h3 class="text-h4 font-weight-bold">{{ statistics.low_stock_count }}</h3>
            </div>
            <v-icon size="48" color="blue-darken-2">mdi-alert</v-icon>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Nilai Inventori</p>
              <h3 class="text-h5 font-weight-bold">{{ formatRp(statistics.inventory_value) }}</h3>
            </div>
            <v-icon size="48" color="blue-darken-3">mdi-cash-multiple</v-icon>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Transaction Summary -->
    <v-row class="mt-4">
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2" color="blue">mdi-arrow-down-bold</v-icon>
            Barang Masuk
          </v-card-title>
          <v-card-text>
            <div class="d-flex justify-space-between align-center mb-2">
              <span class="text-body-2 text-medium-emphasis">Total Transaksi</span>
              <span class="text-h6 font-weight-bold">{{ statistics.inbound_count }}</span>
            </div>
            <v-divider class="my-2" />
            <div class="d-flex justify-space-between align-center">
              <span class="text-body-2 text-medium-emphasis">Total Nilai</span>
              <span class="text-h6 font-weight-bold text-blue">{{ formatRp(statistics.inbound_value) }}</span>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2" color="blue">mdi-arrow-up-bold</v-icon>
            Barang Keluar
          </v-card-title>
          <v-card-text>
            <div class="d-flex justify-space-between align-center mb-2">
              <span class="text-body-2 text-medium-emphasis">Total Transaksi</span>
              <span class="text-h6 font-weight-bold">{{ statistics.outbound_count }}</span>
            </div>
            <v-divider class="my-2" />
            <div class="d-flex justify-space-between align-center">
              <span class="text-body-2 text-medium-emphasis">Total Nilai</span>
              <span class="text-h6 font-weight-bold text-blue">{{ formatRp(statistics.outbound_value) }}</span>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Pengeluaran Per Item Table -->
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="d-flex align-center">
              Pengeluaran Per Item
            </div>
            <v-btn :href="route('expenditures.items.export.xlsx')" color="grey" size="small" variant="plain" :disabled="expenditureLoading">
              <v-icon start>mdi-download</v-icon>
               SpreadSheet
            </v-btn>
          </v-card-title>

          <v-card-text>
            <!-- Search and Date Filter -->
            <v-row class="mb-3">
              <v-col cols="12" md="6">
                <v-text-field v-model="searchItemOutbound" density="compact" label="Cari"
                  hint="Cari berdasarkan Nama Barang"
                  prepend-inner-icon="mdi-magnify" clearable :loading="itemExpenditureLoading"
                  placeholder="Ketik untuk mencari..." />
              </v-col>
              <v-col cols="6" md="2">
                <DateTimePickerInput v-model="itemExpenditureStartDate" label="Mulai" density="compact"
                  :loading="itemExpenditureLoading" />
              </v-col>
              <v-col cols="6" md="2">
                <DateTimePickerInput v-model="itemExpenditureEndDate" label="Sampai" density="compact"
                  :loading="itemExpenditureLoading" />
              </v-col>
              <v-col cols="12" md="2" class="text-right">
                <div class="text-body-2 text-medium-emphasis">Total Nilai Pengeluaran</div>
                <div class="text-h6 font-weight-bold text-blue">{{ formatRp(totalItemExpenditureValue) }}</div>
              </v-col>
            </v-row>

            <!-- Table with Loading State -->
            <v-progress-linear v-if="itemExpenditureLoading" indeterminate color="primary" class="mb-3" />

            <v-table density="comfortable" class="[&_td]:border-none!" striped="even" hover>
              <thead>
                <tr>
                  <th class="text-left">Nama Barang</th>
                  <th class="text-center">Jumlah</th>
                  <th class="text-right">Harga/Unit</th>
                  <th class="text-right">Total Pengeluaran</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in itemExpenditureData" :key="item.itemName">
                  <td class="font-weight-medium">{{ item.itemName }}</td>
                  <td class="text-center font-weight-bold">
                    <v-chip size="small" :color="item.count > 0 ? 'blue' : 'grey'">
                      {{ item.count }} {{ item.measurementUnit }}
                    </v-chip>
                  </td>
                  <td class="text-right">{{ formatRp(item.pricePerUnit) }}</td>
                  <td class="text-right font-weight-bold" :class="item.expenditure > 0 ? 'text-blue' : 'text-grey'">
                    {{ formatRp(item.expenditure) }}
                  </td>
                </tr>
                <tr v-if="itemExpenditureData.length === 0 && !itemExpenditureLoading">
                  <td colspan="4" class="text-center text-medium-emphasis py-8">
                    <v-icon size="48" color="grey-lighten-1">mdi-database-off</v-icon>
                    <div class="mt-2">
                      {{ searchItemOutbound ? 'Tidak ada data yang sesuai dengan pencarian' : 'Tidak ada data pengeluaran'
                      }}
                    </div>
                    <div v-if="searchItemOutbound" class="text-caption mt-1">
                      Coba kata kunci lain atau hapus filter pencarian
                    </div>
                  </td>
                </tr>
              </tbody>
            </v-table>

            <!-- Pagination -->
            <div v-if="itemExpenditureData.length > 0" class="d-flex justify-end align-center mt-4">
              <v-pagination v-model="itemExpenditurePage" :length="itemExpenditureLastPage" :total-visible="7"
                density="comfortable" :disabled="itemExpenditureLoading" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Pengeluaran Per-SKU Table -->
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="d-flex align-center">
              Pengeluaran Per SKU
            </div>
            <v-btn :href="route('expenditures.skus.export.xlsx')" color="grey" size="small" variant="plain" :disabled="expenditureLoading">
              <v-icon start>mdi-download</v-icon>
               SpreadSheet
            </v-btn>
          </v-card-title>

          <v-card-text>
            <!-- Search and Date Filter -->
            <v-row class="mb-3">
              <v-col cols="12" md="6">
                <v-text-field v-model="searchOutbound" density="compact" label="Cari"
                  hint="Cari berdasarkan Nama Barang, SKU dan Spesifikasi"
                  prepend-inner-icon="mdi-magnify" clearable :loading="expenditureLoading"
                  placeholder="Ketik untuk mencari..." />
              </v-col>
              <v-col cols="6" md="2">
                <DateTimePickerInput v-model="expenditureStartDate" label="Mulai" density="compact"
                  :loading="expenditureLoading" />
              </v-col>
              <v-col cols="6" md="2">
                <DateTimePickerInput v-model="expenditureEndDate" label="Sampai" density="compact"
                  :loading="expenditureLoading" />
              </v-col>
              <v-col cols="12" md="2" class="text-right">
                <div class="text-body-2 text-medium-emphasis">Total Nilai Pengeluaran</div>
                <div class="text-h6 font-weight-bold text-blue">{{ formatRp(totalExpenditureValue) }}</div>
              </v-col>
            </v-row>

            <!-- Table with Loading State -->
            <v-progress-linear v-if="expenditureLoading" indeterminate color="primary" class="mb-3" />

            <v-table density="comfortable" class="[&_td]:border-none!" striped="even" hover>
              <thead>
                <tr>
                  <th class="text-left">Nama Barang</th>
                  <th class="text-left">SKU</th>
                  <th class="text-left">Spesifikasi</th>
                  <th class="text-center">Jumlah</th>
                  <th class="text-right">Harga/Unit</th>
                  <th class="text-right">Total Pengeluaran</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in expenditureData" :key="item.sku">
                  <td class="font-weight-medium">{{ item.itemName }}</td>
                  <td>
                    <code class="text-caption bg-grey-lighten-3 pa-1 rounded">{{ item.sku }}</code>
                  </td>
                  <td>{{ item.spesificationName }}</td>
                  <td class="text-center font-weight-bold">
                    <v-chip size="small" :color="item.count > 0 ? 'blue' : 'grey'">
                      {{ item.count }} {{ item.measurementUnit }}
                    </v-chip>
                  </td>
                  <td class="text-right">{{ formatRp(item.pricePerUnit) }}</td>
                  <td class="text-right font-weight-bold" :class="item.expenditure > 0 ? 'text-blue' : 'text-grey'">
                    {{ formatRp(item.expenditure) }}
                  </td>
                </tr>
                <tr v-if="expenditureData.length === 0 && !expenditureLoading">
                  <td colspan="6" class="text-center text-medium-emphasis py-8">
                    <v-icon size="48" color="grey-lighten-1">mdi-database-off</v-icon>
                    <div class="mt-2">
                      {{ searchOutbound ? 'Tidak ada data yang sesuai dengan pencarian' : 'Tidak ada data pengeluaran'
                      }}
                    </div>
                    <div v-if="searchOutbound" class="text-caption mt-1">
                      Coba kata kunci lain atau hapus filter pencarian
                    </div>
                  </td>
                </tr>
              </tbody>
            </v-table>

            <!-- Pagination -->
            <div v-if="expenditureData.length > 0" class="d-flex justify-end align-center mt-4">
              <v-pagination v-model="expenditurePage" :length="expenditureLastPage" :total-visible="7"
                density="comfortable" :disabled="expenditureLoading" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Transactions and Low Stock -->
    <v-row class="mt-4">
      <!-- Recent Transactions -->
      <v-col cols="12" md="7">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-history</v-icon>
            Transaksi Terbaru
          </v-card-title>
          <v-card-text>
            <v-table density="comfortable">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Jenis</th>
                  <th>Penerima/Supplier</th>
                  <th class="text-right">Nilai</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="transaction in recent_transactions" :key="transaction.id">
                  <td>{{ formatDateIndonesia(transaction.transaction_date) }}</td>
                  <td>
                    <v-chip :color="transaction.type === 'in' ? 'blue-darken-2' : 'blue-lighten-1'" size="small" variant="tonal">
                      <v-icon start size="small">
                        {{ transaction.type === 'in' ? 'mdi-arrow-down' : 'mdi-arrow-up' }}
                      </v-icon>
                      {{ transaction.type === 'in' ? 'Masuk' : 'Keluar' }}
                    </v-chip>
                  </td>
                  <td>{{ transaction.recipient || transaction.supplier || '-' }}</td>
                  <td class="text-right">{{ formatRp(transaction.total_value) }}</td>
                </tr>
                <tr v-if="recent_transactions.length === 0">
                  <td colspan="4" class="text-center text-medium-emphasis">
                    Tidak ada transaksi
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Low Stock Items -->
      <v-col cols="12" md="5">
        <v-card class="h-full">
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2" color="orange">mdi-alert-circle</v-icon>
            Stok Menipis
          </v-card-title>
          <v-card-text>
            <v-list density="compact">
              <v-list-item v-for="item in low_stock_items" :key="item.id" class="px-0">
                <v-list-item-title>{{ item.item_name }}</v-list-item-title>
                <v-list-item-subtitle>
                  {{ item.sku }} • {{ item.specification }}
                </v-list-item-subtitle>
                <template v-slot:append>
                  <v-chip :color="item.quantity <= 5 ? 'red' : 'orange'" size="small">
                    {{ item.quantity }} {{ item.unit }}
                  </v-chip>
                </template>
              </v-list-item>
              <v-list-item v-if="low_stock_items.length === 0">
                <v-list-item-title class="text-center text-medium-emphasis">
                  Semua stok aman
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Top Recipients -->
    <v-row class="mt-4 mb-8">
      <v-col cols="12">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-account-group</v-icon>
            Penerima Teratas
          </v-card-title>
          <v-card-text>
            <v-list density="compact">
              <v-list-item v-for="(recipient, index) in top_recipients" :key="recipient.id" class="px-0">
                <template v-slot:prepend>
                  <v-avatar :color="'blue-' + ((index + 1) * 100)" size="32" class="mr-3">
                    <span class="text-white">{{ index + 1 }}</span>
                  </v-avatar>
                </template>
                <v-list-item-title>{{ recipient.name }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip size="small">
                    {{ recipient.transaction_count }} transaksi
                  </v-chip>
                </template>
              </v-list-item>
              <v-list-item v-if="top_recipients.length === 0">
                <v-list-item-title class="text-center text-medium-emphasis">
                  Tidak ada data
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.chart-container {
  position: relative;
}

code {
  font-family: 'Courier New', monospace;
  font-size: 0.85em;
}
</style>
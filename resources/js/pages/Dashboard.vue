<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Filler,
  Legend,
  LinearScale,
  LineElement,
  PointElement,
  Title,
  Tooltip,
} from 'chart.js';
import { computed, onMounted, ref } from 'vue';
import { Bar, Doughnut, Line, Pie } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler,
);

defineOptions({ layout: ApplicationLayout });

// ─── State ────────────────────────────────────────────────────────────────────

const loading = ref({
  summary: true,
  perItem: true,
  perUnit: true,
  monthly: true,
  topItems: true,
  statusDist: true,
  recentRequests: true,
});

const errors = ref({
  summary: false,
  perItem: false,
  perUnit: false,
  monthly: false,
  topItems: false,
  statusDist: false,
  recentRequests: false,
});

const summary = ref({ totalItems: 0, totalRequests: 0, pendingRequests: 0, totalAdditions: 0 });
const recentRequests = ref([]);

// Chart data — empty by default
const perItemChart = ref({ labels: [], datasets: [] });
const perUnitChart = ref({ labels: [], datasets: [] });
const monthlyChart = ref({ labels: [], datasets: [] });
const topItemsChart = ref({ labels: [], datasets: [] });
const statusDistChart = ref({ labels: [], datasets: [] });

// => Palette & Defaults
const palette = [
  '#4F86F7',
  '#FF6B6B',
  '#43D9AD',
  '#FFB547',
  '#A78BFA',
  '#38BDF8',
  '#FB923C',
  '#34D399',
  '#F472B6',
  '#FACC15',
];

/** @type {import("chart.js").ChartOptions} */
const chartDefaults = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: { color: '#94a3b8', font: { family: 'DM Sans', size: 12 } },
    },
    tooltip: {
      backgroundColor: '#1e293b',
      titleColor: '#e2e8f0',
      bodyColor: '#94a3b8',
      borderColor: '#334155',
      borderWidth: 1,
      padding: 10,
    },
  },
  locale: 'id-ID',
  scales: {
    x: {
      ticks: { color: '#64748b', font: { family: 'DM Sans' } },
      grid: { color: '#1e293b' },
    },
    y: {
      ticks: { color: '#64748b', font: { family: 'DM Sans' } },
      grid: { color: '#1e293b' },
    },
  },
};

// => API Calls
async function fetchSummary() {
  loading.value.summary = true;
  errors.value.summary = false;
  try {
    const { data } = await axios.get('/api/dashboard/summary');
    summary.value = data;
  } catch {
    errors.value.summary = true;
  } finally {
    loading.value.summary = false;
  }
}

async function fetchPerItem() {
  loading.value.perItem = true;
  errors.value.perItem = false;
  try {
    const { data } = await axios.get('/api/dashboard/expenditures/items');
    if (!data.data?.length) {
      perItemChart.value = { labels: [], datasets: [] };
      return;
    }
    perItemChart.value = {
      labels: data.data.map((d) => d.name),
      datasets: [
        {
          label: 'Pengeluaran',
          data: data.data.map((d) => d.totalPrice),
          backgroundColor: data.data.map((_, i) => palette[i % palette.length] + 'cc'),
          borderColor: data.data.map((_, i) => palette[i % palette.length]),
          borderWidth: 1,
          borderRadius: 6,
        },
      ],
    };
  } catch {
    errors.value.perItem = true;
    perItemChart.value = { labels: [], datasets: [] };
  } finally {
    loading.value.perItem = false;
  }
}

async function fetchPerUnit() {
  loading.value.perUnit = true;
  errors.value.perUnit = false;
  try {
    const { data } = await axios.get('/api/dashboard/expenditures/units');
    if (!data.data?.length) {
      perUnitChart.value = { labels: [], datasets: [] };
      return;
    }
    perUnitChart.value = {
      labels: data.data.map((d) => d.name),
      datasets: [
        {
          label: 'Pengeluaran',
          data: data.data.map((d) => d.totalPrice),
          backgroundColor: data.data.map((_, i) => palette[i % palette.length] + 'bb'),
          borderColor: data.data.map((_, i) => palette[i % palette.length]),
          borderWidth: 2,
          borderRadius: 8,
        },
      ],
    };
  } catch {
    errors.value.perUnit = true;
    perUnitChart.value = { labels: [], datasets: [] };
  } finally {
    loading.value.perUnit = false;
  }
}

async function fetchMonthly() {
  loading.value.monthly = true;
  errors.value.monthly = false;
  try {
    const { data } = await axios.get('/api/dashboard/expenditures/monthly');
    if (!data.data?.length) {
      monthlyChart.value = { labels: [], datasets: [] };
      return;
    }
    monthlyChart.value = {
      labels: data.data.map((d) => d.month),
      datasets: [
        {
          label: 'Nilai Pengeluaran',
          data: data.data.map((d) => d.totalValue),
          borderColor: '#43D9AD',
          backgroundColor: 'rgba(67,217,173,0.08)',
          tension: 0.4,
          fill: true,
          pointBackgroundColor: '#43D9AD',
          pointRadius: 5,
          yAxisID: 'y2',
        },
      ],
    };
  } catch {
    errors.value.monthly = true;
    monthlyChart.value = { labels: [], datasets: [] };
  } finally {
    loading.value.monthly = false;
  }
}

async function fetchTopItems() {
  loading.value.topItems = true;
  errors.value.topItems = false;
  try {
    const { data } = await axios.get('/api/dashboard/items/tops', { length: 5 });
    if (!data.data?.length) {
      topItemsChart.value = { labels: [], datasets: [] };
      return;
    }
    topItemsChart.value = {
      labels: data.data.map((d) => d.name),
      datasets: [
        {
          data: data.data.map((d) => d.quantity),
          backgroundColor: data.data.map((_, i) => palette[i % palette.length] + 'dd'),
          borderColor: '#0f172a',
          borderWidth: 2,
        },
      ],
    };
  } catch {
    errors.value.topItems = true;
    topItemsChart.value = { labels: [], datasets: [] };
  } finally {
    loading.value.topItems = false;
  }
}

async function fetchStatusDist() {
  loading.value.statusDist = true;
  errors.value.statusDist = false;
  try {
    const { data } = await axios.get(route('api.dashboards.requests.status.count'));
    if (!data?.data?.length) {
      statusDistChart.value = { labels: [], datasets: [] };
      return;
    }
    statusDistChart.value = {
      labels: data.data.map((d) => d.status),
      datasets: [
        {
          data: data.data.map((d) => d.count),
          backgroundColor: ['#43D9AD', '#FFB547', '#FF6B6B', '#4F86F7'],
          borderColor: '#0f172a',
          borderWidth: 2,
        },
      ],
    };
  } catch {
    errors.value.statusDist = true;
    statusDistChart.value = { labels: [], datasets: [] };
  } finally {
    loading.value.statusDist = false;
  }
}

async function fetchRecentRequests() {
  loading.value.recentRequests = true;
  errors.value.recentRequests = false;
  try {
    const { data } = await axios.get('/api/dashboard/requests/recents');
    recentRequests.value = data.data ?? [];
  } catch {
    errors.value.recentRequests = true;
    recentRequests.value = [];
  } finally {
    loading.value.recentRequests = false;
  }
}

function refreshAll() {
  fetchSummary();
  fetchPerItem();
  fetchPerUnit();
  fetchMonthly();
  fetchTopItems();
  fetchStatusDist();
  fetchRecentRequests();
}

// => Chart Options
const barOptions = computed(() => ({
  ...chartDefaults,
  scales: {
    ...chartDefaults.scales,
    y: {
      ...chartDefaults.scales.y,
      ticks: {
        ...chartDefaults.scales.y.ticks,
        format: { style: 'currency', currency: 'IDR', maximumFractionDigits: 2, minimumFractionDigits: 0 },
      },
    },
  },
  plugins: { ...chartDefaults.plugins, legend: { display: false } },
}));

const horizontalBarOptions = computed(() => ({
  ...chartDefaults,
  indexAxis: 'y',
  scales: {
    ...chartDefaults.scales,
    x: {
      ...chartDefaults.scales.x,
      ticks: {
        ...chartDefaults.scales.x.ticks,
        format: { style: 'currency', currency: 'IDR', maximumFractionDigits: 2, minimumFractionDigits: 0 },
      },
    },
  },
  plugins: { ...chartDefaults.plugins, legend: { display: false } },
}));

const monthlyOptions = computed(() => ({
  ...chartDefaults,
  interaction: { mode: 'index', intersect: false },
  scales: {
    ...chartDefaults.scales,
    y: null,
    y2: {
      position: 'right',
      ticks: {
        color: '#43D9AD',
        font: { family: 'DM Sans' },
        format: { style: 'currency', currency: 'IDR', maximumFractionDigits: 2, minimumFractionDigits: 0 },
      },
      grid: { display: true },
    },
  },
}));

const doughnutOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '68%',
  plugins: {
    legend: { position: 'bottom', labels: { color: '#94a3b8', font: { family: 'DM Sans', size: 12 }, padding: 16 } },
    tooltip: chartDefaults.plugins.tooltip,
  },
}));

const pieOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { color: '#94a3b8', font: { family: 'DM Sans', size: 12 }, padding: 16 } },
    tooltip: chartDefaults.plugins.tooltip,
  },
}));

// => Computed Helpers
const hasPerItemData = computed(() => perItemChart.value.labels?.length > 0);
const hasPerUnitData = computed(() => perUnitChart.value.labels?.length > 0);
const hasMonthlyData = computed(() => monthlyChart.value.labels?.length > 0);
const hasTopItemsData = computed(() => topItemsChart.value.labels?.length > 0);
const hasStatusDistData = computed(() => statusDistChart.value.labels?.length > 0);

const statusColor = (s) => ({ accepted: 'success', pending: 'secondary', rejected: 'error' })[s] || 'default';
const statusLabel = (s) => ({ accepted: 'Diterima', pending: 'Menunggu', rejected: 'Ditolak' })[s] || s;

const summaryCards = computed(() => [
  {
    title: 'Total Barang',
    value: summary.value.totalItems,
    icon: 'mdi-package-variant-closed',
    color: '#4F86F7',
    bg: 'rgba(79,134,247,0.12)',
  },
  {
    title: 'Total Permintaan',
    value: summary.value.totalRequests,
    icon: 'mdi-clipboard-list-outline',
    color: '#43D9AD',
    bg: 'rgba(67,217,173,0.12)',
  },
  {
    title: 'Permintaan Pending',
    value: summary.value.pendingRequests,
    icon: 'mdi-clock-outline',
    color: '#FFB547',
    bg: 'rgba(255,181,71,0.12)',
  },
  {
    title: 'Penambahan Barang',
    value: summary.value.totalAdditions,
    icon: 'mdi-package-variant-plus',
    color: '#A78BFA',
    bg: 'rgba(167,139,250,0.12)',
  },
]);

onMounted(refreshAll);
</script>

<template>
  <Head>
    <title>Dashboard</title>
  </Head>
  <div class="min-h-dvh! p-6! font-[DM_Sans,Plus_Jakarta_Sans,sans-serif]">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1
          class="font-[Plus_Jakarta_Sans,sans-serif]! text-[1.75rem] leading-[1.2] font-extrabold text-[rgb(var(--v-theme-on-surface))]">
          Dashboard
        </h1>
        <p class="mt-0.5 font-[#64748b] text-sm!">Ringkasan aktivitas gudang ATK</p>
      </div>
      <v-btn variant="tonal" color="primary" prepend-icon="mdi-refresh" @click="refreshAll"> Refresh </v-btn>
    </div>

    <!-- Summary Cards -->
    <v-row class="mb-2">
      <v-col v-for="card in summaryCards" :key="card.title" cols="12" sm="6" lg="3">
        <v-card class="summary-card h-full w-full" variant="flat" rounded="lg">
          <v-card-text class="d-flex align-center ga-4 pa-5">
            <div class="card-icon-wrap" :style="{ background: card.bg }">
              <v-icon :color="card.color" size="26" :icon="card.icon" />
            </div>
            <div>
              <div class="card-label">{{ card.title }}</div>
              <div class="card-value" :style="{ color: card.color }">
                <span v-if="loading.summary">—</span>
                <span v-else-if="errors.summary" class="card-value-error">
                  <v-icon size="18" color="error" icon="mdi-alert-circle-outline" />
                </span>
                <span v-else>{{ card.value.toLocaleString() }}</span>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Monthly Expenditures -->
    <v-row class="mb-2">
      <v-col cols="12">
        <v-card class="chart-card" variant="flat" rounded="lg">
          <v-card-title class="chart-title">
            <v-icon class="mr-2" color="primary">mdi-chart-line</v-icon>
            Pengeluaran Bulanan
          </v-card-title>
          <v-card-text>
            <div class="chart-wrap chart-wrap--tall">
              <div v-if="loading.monthly" class="chart-loader">
                <v-progress-circular indeterminate color="primary" size="36" />
              </div>
              <div v-else-if="errors.monthly" class="chart-empty">
                <v-icon size="40" color="error" class="mb-2" icon="mdi-wifi-off" />
                <p class="empty-title">Gagal memuat data</p>
                <p class="empty-sub">Periksa koneksi atau coba refresh halaman</p>
                <v-btn
                  size="small"
                  variant="tonal"
                  color="error"
                  class="mt-3"
                  prepend-icon="mdi-refresh"
                  @click="fetchMonthly">
                  Coba Lagi
                </v-btn>
              </div>
              <div v-else-if="!hasMonthlyData" class="chart-empty">
                <v-icon size="40" color="secondary" class="mb-2" icon="mdi-chart-line-variant" />
                <p class="empty-title">Belum ada data pengeluaran</p>
                <p class="empty-sub">Data akan muncul setelah ada transaksi keluar</p>
              </div>
              <Line v-else :data="monthlyChart" :options="monthlyOptions" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Per Item & Per Unit -->
    <v-row class="mb-2">
      <v-col cols="12" md="7">
        <v-card class="chart-card" variant="flat" rounded="lg" height="100%">
          <v-card-title class="chart-title">
            <v-icon class="mr-2" color="primary" icon="mdi-package-variant" />
            Pengeluaran per Barang (Bulan ini)
          </v-card-title>
          <v-card-text>
            <div class="chart-wrap">
              <div v-if="loading.perItem" class="chart-loader">
                <v-progress-circular indeterminate color="primary" size="36" />
              </div>
              <div v-else-if="errors.perItem" class="chart-empty">
                <v-icon size="36" color="error" class="mb-2" icon="mdi-wifi-off" />
                <p class="empty-title">Gagal memuat data</p>
                <p class="empty-sub">Tidak dapat terhubung ke server</p>
                <v-btn
                  size="small"
                  variant="tonal"
                  color="error"
                  class="mt-3"
                  prepend-icon="mdi-refresh"
                  @click="fetchPerItem">
                  Coba Lagi
                </v-btn>
              </div>
              <div v-else-if="!hasPerItemData" class="chart-empty">
                <v-icon size="36" color="secondary" class="mb-2" icon="mdi-package-variant-closed" />
                <p class="empty-title">Tidak ada pengeluaran bulan ini</p>
                <p class="empty-sub">Belum ada barang yang dikeluarkan</p>
              </div>
              <Bar v-else :data="perItemChart" :options="barOptions" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="5">
        <v-card class="chart-card" variant="flat" rounded="lg" height="100%">
          <v-card-title class="chart-title">
            <v-icon class="mr-2" color="secondary" icon="mdi-office-building-outline" />
            Pengeluaran per Unit (Bulan ini)
          </v-card-title>
          <v-card-text>
            <div class="chart-wrap">
              <div v-if="loading.perUnit" class="chart-loader">
                <v-progress-circular indeterminate color="secondary" size="36" />
              </div>
              <div v-else-if="errors.perUnit" class="chart-empty">
                <v-icon size="36" color="error" class="mb-2" icon="mdi-wifi-off" />
                <p class="empty-title">Gagal memuat data</p>
                <p class="empty-sub">Tidak dapat terhubung ke server</p>
                <v-btn
                  size="small"
                  variant="tonal"
                  color="error"
                  class="mt-3"
                  prepend-icon="mdi-refresh"
                  @click="fetchPerUnit">
                  Coba Lagi
                </v-btn>
              </div>
              <div v-else-if="!hasPerUnitData" class="chart-empty">
                <v-icon size="36" color="secondary" class="mb-2" icon="mdi-office-building-outline" />
                <p class="empty-title">Tidak ada data per unit</p>
                <p class="empty-sub">Belum ada permintaan yang diproses</p>
              </div>
              <Bar v-else :data="perUnitChart" :options="horizontalBarOptions" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Doughnut, Pie & Recent Requests -->
    <v-row class="mb-2">
      <!-- Status Distribution -->
      <v-col cols="12" sm="6" md="4">
        <v-card class="chart-card" variant="flat" rounded="lg" height="100%">
          <v-card-title class="chart-title">
            <v-icon class="mr-2" color="success" icon="mdi-chart-donut" />
            Status Permintaan ({{ new Date().getFullYear() }})
          </v-card-title>
          <v-card-text>
            <div class="chart-wrap chart-wrap--donut">
              <div v-if="loading.statusDist" class="chart-loader">
                <v-progress-circular indeterminate color="success" size="36" />
              </div>
              <div v-else-if="errors.statusDist" class="chart-empty">
                <v-icon size="36" color="error" class="mb-2" icon="mdi-wifi-off" />
                <p class="empty-title">Gagal memuat data</p>
                <v-btn
                  size="small"
                  variant="tonal"
                  color="error"
                  class="mt-3"
                  prepend-icon="mdi-refresh"
                  @click="fetchStatusDist">
                  Coba Lagi
                </v-btn>
              </div>
              <div v-else-if="!hasStatusDistData" class="chart-empty">
                <v-icon size="36" color="secondary" class="mb-2" icon="mdi-chart-donut-variant" />
                <p class="empty-title">Belum ada permintaan</p>
                <p class="empty-sub">Status permintaan akan tampil di sini</p>
              </div>
              <Doughnut v-else :data="statusDistChart" :options="doughnutOptions" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Top Items -->
      <v-col cols="12" sm="6" md="4">
        <v-card class="chart-card" variant="flat" rounded="lg" height="100%">
          <v-card-title class="chart-title">
            <v-icon class="mr-2" color="warning" icon="mdi-podium" />
            Top 5 Barang Diminta Bulan Ini
          </v-card-title>
          <v-card-text>
            <div class="chart-wrap chart-wrap--donut">
              <div v-if="loading.topItems" class="chart-loader">
                <v-progress-circular indeterminate color="warning" size="36" />
              </div>
              <div v-else-if="errors.topItems" class="chart-empty">
                <v-icon size="36" color="error" class="mb-2" icon="mdi-wifi-off" />
                <p class="empty-title">Gagal memuat data</p>
                <v-btn
                  size="small"
                  variant="tonal"
                  color="error"
                  class="mt-3"
                  prepend-icon="mdi-refresh"
                  @click="fetchTopItems">
                  Coba Lagi
                </v-btn>
              </div>
              <div v-else-if="!hasTopItemsData" class="chart-empty">
                <v-icon size="36" color="secondary" class="mb-2" icon="mdi-podium-silver" />
                <p class="empty-title">Belum ada data bulan ini</p>
                <p class="empty-sub">Data top barang akan muncul setelah ada permintaan</p>
              </div>
              <Pie v-else :data="topItemsChart" :options="pieOptions" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Recent Requests -->
      <v-col cols="12" md="4">
        <v-card class="chart-card" variant="flat" rounded="lg" height="100%">
          <v-card-title class="chart-title">
            <v-icon class="mr-2" color="info" icon="mdi-clipboard-clock-outline" />
            Permintaan Terbaru
          </v-card-title>
          <v-card-text class="pa-0">
            <div v-if="loading.recentRequests" class="chart-loader" style="height: 200px">
              <v-progress-circular indeterminate color="info" size="36" />
            </div>
            <div v-else-if="errors.recentRequests" class="chart-empty" style="height: 200px">
              <v-icon size="36" color="error" class="mb-2" icon="mdi-wifi-off" />
              <p class="empty-title">Gagal memuat permintaan</p>
              <v-btn
                size="small"
                variant="tonal"
                color="error"
                class="mt-3"
                prepend-icon="mdi-refresh"
                @click="fetchRecentRequests">
                Coba Lagi
              </v-btn>
            </div>
            <div v-else-if="!recentRequests.length" class="chart-empty" style="height: 200px">
              <v-icon size="36" color="secondary" class="mb-2" icon="mdi-clipboard-check-outline" />
              <p class="empty-title">Belum ada permintaan</p>
              <p class="empty-sub">Permintaan terbaru akan tampil di sini</p>
            </div>
            <v-list v-else bg-color="transparent" lines="two">
              <v-list-item
                v-for="req in recentRequests"
                :key="req.id"
                :title="req.requester"
                rounded="lg"
                class="req-item mx-2 mb-1">
                <template #subtitle>
                  <v-chip v-for="item in req.items" :key="item.name" density="comfortable" size="x-small" class="ms-1"
                    >{{ item.name }} {{ item.quantity }} {{ item.unit }}</v-chip
                  >
                </template>
                <template #append>
                  <div class="flex flex-col! items-center">
                    <v-chip :color="statusColor(req.status)" size="x-small" variant="tonal">
                      {{ statusLabel(req.status) }}
                    </v-chip>
                    <span class="text-xs">{{ req.date }}</span>
                  </div>
                </template>
                <template #prepend>
                  <v-avatar size="34" :color="statusColor(req.status)" variant="tonal">
                    <span style="font-size: 11px; font-weight: 700">{{ req.requester.charAt(0) }}</span>
                  </v-avatar>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<style scoped>
/* Summary Cards */
.summary-card {
  background: rgba(var(--v-theme-surface-variant), 0.04) !important;
  border: 1px solid rgba(255, 255, 255, 0.06);
  transition:
    transform 0.2s,
    box-shadow 0.2s;
}
.summary-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
}
.card-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.card-label {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.card-value {
  font-size: 1.75rem;
  font-weight: 700;
  font-family: 'Plus Jakarta Sans', sans-serif;
  line-height: 1.2;
}
.card-value-error {
  font-size: 1rem;
}

/* Chart Cards */
.chart-card {
  background: rgba(var(--v-theme-surface-variant), 0.05) !important;
  border: 1px solid rgba(255, 255, 255, 0.06);
}
.chart-title {
  font-family: 'DM Sans', sans-serif;
  font-size: 0.95rem !important;
  font-weight: 600;
  padding: 16px 20px 8px;
  display: flex;
  align-items: center;
}
.chart-wrap {
  position: relative;
  height: 280px;
}
.chart-wrap--tall {
  height: 320px;
}
.chart-wrap--donut {
  height: 260px;
}
.chart-loader {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
}

/* Empty / Error State */
.chart-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
  padding: 16px;
}
.empty-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: #64748b;
  margin: 0;
}
.empty-sub {
  font-size: 0.78rem;
  color: #475569;
  margin: 4px 0 0;
}

/* Recent requests list */
.req-item {
  transition: background 0.15s;
}
.req-item:hover {
  background: rgba(255, 255, 255, 0.04) !important;
}
</style>

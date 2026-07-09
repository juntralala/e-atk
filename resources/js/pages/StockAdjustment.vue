<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  adjustments: {
    type: Array,
    default: () => [],
  },
  canApprove: {
    type: Boolean,
    default: false,
  },
});

// ---- Data dummy (hapus setelah integrasi backend) ----
const dummyAdjustments = [
  {
    id: 1,
    created_at: '2026-05-06T08:30:00',
    created_by: 'Budi Santoso',
    notes: 'Stok opname bulanan — ada beberapa item yang jumlahnya tidak sesuai sistem.',
    status: 'pending',
    items: [
      {
        old_stock: 120,
        new_stock: 95,
        item: { name: 'Paracetamol 500mg', unit: { name: 'strip' } },
        reason: { reason: 'Kedaluwarsa' },
      },
      {
        old_stock: 300,
        new_stock: 280,
        item: { name: 'Amoxicillin 250mg', unit: { name: 'kapsul' } },
        reason: { reason: 'Rusak/pecah' },
      },
      {
        old_stock: 200,
        new_stock: 230,
        item: { name: 'Vitamin C 1000mg', unit: { name: 'tablet' } },
        reason: { reason: 'Temuan stok' },
      },
    ],
  },
  {
    id: 2,
    created_at: '2026-05-05T14:15:00',
    created_by: 'Siti Rahayu',
    notes: '',
    status: 'pending',
    items: [
      {
        old_stock: 45,
        new_stock: 40,
        item: { name: 'Alkohol 70%', unit: { name: 'botol' } },
        reason: { reason: 'Tumpah/bocor' },
      },
    ],
  },
  {
    id: 3,
    created_at: '2026-05-04T10:00:00',
    created_by: 'Agus Pramono',
    notes: 'Koreksi setelah pengecekan fisik gudang.',
    status: 'approved',
    items: [
      {
        old_stock: 60,
        new_stock: 55,
        item: { name: 'Betadine 30ml', unit: { name: 'botol' } },
        reason: { reason: 'Kedaluwarsa' },
      },
      {
        old_stock: 500,
        new_stock: 470,
        item: { name: 'Masker Bedah', unit: { name: 'pcs' } },
        reason: { reason: 'Rusak/pecah' },
      },
    ],
  },
  {
    id: 4,
    created_at: '2026-05-03T09:45:00',
    created_by: 'Dewi Lestari',
    notes: 'Penyesuaian stok setelah audit internal.',
    status: 'rejected',
    items: [
      {
        old_stock: 150,
        new_stock: 200,
        item: { name: 'Ibuprofen 400mg', unit: { name: 'tablet' } },
        reason: { reason: 'Temuan stok' },
      },
    ],
  },
  {
    id: 5,
    created_at: '2026-05-02T11:20:00',
    created_by: 'Rudi Hermawan',
    notes: '',
    status: 'approved',
    items: [
      {
        old_stock: 80,
        new_stock: 75,
        item: { name: 'Infus NaCl 500ml', unit: { name: 'kantong' } },
        reason: { reason: 'Rusak/pecah' },
      },
      {
        old_stock: 30,
        new_stock: 28,
        item: { name: 'Spuit 5ml', unit: { name: 'pcs' } },
        reason: { reason: 'Kedaluwarsa' },
      },
    ],
  },
];

// Gunakan dummy jika props kosong (development), pakai props saat production
const adjustmentData = computed(() => (props.adjustments.length > 0 ? props.adjustments : dummyAdjustments));

// ---- Tabel ----
const search = ref('');
const statusFilter = ref('pending');

const statusOptions = [
  { title: 'Semua', value: '' },
  { title: 'Menunggu', value: 'pending' },
  { title: 'Disetujui', value: 'approved' },
  { title: 'Ditolak', value: 'rejected' },
];

const headers = [
  { title: '#', key: 'index', width: 60, sortable: false },
  { title: 'Tanggal', key: 'created_at' },
  { title: 'Diajukan Oleh', key: 'created_by' },
  { title: 'Jumlah Item', key: 'items_count', align: 'center' },
  { title: 'Catatan', key: 'notes', sortable: false },
  { title: 'Status', key: 'status', align: 'center' },
  { title: 'Aksi', key: 'actions', sortable: false, align: 'center' },
];

const filteredAdjustments = computed(() => {
  return adjustmentData.value.filter((a) => {
    const matchStatus = statusFilter.value === '' || a.status === statusFilter.value;
    const matchSearch =
      search.value === '' ||
      a.created_by?.toLowerCase().includes(search.value.toLowerCase()) ||
      a.notes?.toLowerCase().includes(search.value.toLowerCase());
    return matchStatus && matchSearch;
  });
});

function formatDate(dateStr) {
  if (!dateStr) return '-';
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(dateStr));
}

// ---- Status chip ----
const statusConfig = {
  pending: { color: 'orange', icon: 'mdi-clock-outline', label: 'Menunggu' },
  approved: { color: 'green', icon: 'mdi-check-circle-outline', label: 'Disetujui' },
  rejected: { color: 'red', icon: 'mdi-close-circle-outline', label: 'Ditolak' },
};

function getStatus(status) {
  return statusConfig[status] ?? { color: 'grey', icon: 'mdi-help', label: status };
}

// ---- Detail dialog ----
const detailDialog = ref(false);
const selectedAdjustment = ref(null);

function openDetail(adjustment) {
  selectedAdjustment.value = adjustment;
  detailDialog.value = true;
}

// ---- Approve / Reject ----
const successDialog = ref(false);
const successMessage = ref('');
const errorDialog = ref(false);
const errorMessage = ref('');
const processing = ref(false);

function approve(adjustment) {
  // Kalau pakai dummy, simulasikan saja
  if (props.adjustments.length === 0) {
    const target = dummyAdjustments.find((a) => a.id === adjustment.id);
    if (target) target.status = 'approved';
    successMessage.value = 'Pengajuan berhasil disetujui.';
    successDialog.value = true;
    detailDialog.value = false;
    return;
  }

  processing.value = true;
  router.post(
    route('stock-adjustments.approve', adjustment.id),
    {},
    {
      onSuccess: () => {
        successMessage.value = 'Pengajuan berhasil disetujui.';
        successDialog.value = true;
        detailDialog.value = false;
      },
      onError: (errors) => {
        errorMessage.value = errors.error ?? errors.message ?? 'Terjadi kesalahan.';
        errorDialog.value = true;
      },
      onFinish: () => {
        processing.value = false;
      },
    },
  );
}

function reject(adjustment) {
  // Kalau pakai dummy, simulasikan saja
  if (props.adjustments.length === 0) {
    const target = dummyAdjustments.find((a) => a.id === adjustment.id);
    if (target) target.status = 'rejected';
    successMessage.value = 'Pengajuan berhasil ditolak.';
    successDialog.value = true;
    detailDialog.value = false;
    return;
  }

  processing.value = true;
  router.post(
    route('stock-adjustments.reject', adjustment.id),
    {},
    {
      onSuccess: () => {
        successMessage.value = 'Pengajuan berhasil ditolak.';
        successDialog.value = true;
        detailDialog.value = false;
      },
      onError: (errors) => {
        errorMessage.value = errors.error ?? errors.message ?? 'Terjadi kesalahan.';
        errorDialog.value = true;
      },
      onFinish: () => {
        processing.value = false;
      },
    },
  );
}

// ---- Konfirmasi approve/reject ----
const confirmDialog = ref(false);
const confirmAction = ref(null);
const confirmTarget = ref(null);

const confirmConfig = computed(() => {
  if (confirmAction.value === 'approve') {
    return {
      title: 'Setujui Pengajuan?',
      message: 'Stok akan diperbarui setelah disetujui. Lanjutkan?',
      color: 'green',
      icon: 'mdi-check-circle-outline',
      label: 'Setujui',
    };
  }
  return {
    title: 'Tolak Pengajuan?',
    message: 'Pengajuan ini akan ditolak dan stok tidak akan berubah. Lanjutkan?',
    color: 'red',
    icon: 'mdi-close-circle-outline',
    label: 'Tolak',
  };
});

function askConfirm(action, adjustment) {
  confirmAction.value = action;
  confirmTarget.value = adjustment;
  confirmDialog.value = true;
}

function doConfirm() {
  confirmDialog.value = false;
  if (confirmAction.value === 'approve') approve(confirmTarget.value);
  else reject(confirmTarget.value);
}
</script>

<template>
  <v-container fluid class="pa-4 pa-md-6">
    <SuccessDialog v-model="successDialog" :message="successMessage" />
    <AlertDialog title="Gagal!" v-model="errorDialog" :message="errorMessage" />

    <!-- Confirm Dialog -->
    <v-dialog v-model="confirmDialog" max-width="420">
      <v-card rounded="lg">
        <v-card-title class="d-flex align-center gap-2 px-5 pt-5">
          <v-icon :color="confirmConfig.color">{{ confirmConfig.icon }}</v-icon>
          {{ confirmConfig.title }}
        </v-card-title>
        <v-card-text class="px-5">{{ confirmConfig.message }}</v-card-text>
        <v-card-actions class="px-5 pb-5">
          <v-spacer />
          <v-btn variant="outlined" color="grey-darken-1" :disabled="processing" @click="confirmDialog = false">
            Batal
          </v-btn>
          <v-btn variant="flat" :color="confirmConfig.color" :loading="processing" @click="doConfirm">
            {{ confirmConfig.label }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Detail Dialog -->
    <v-dialog v-model="detailDialog" max-width="700" scrollable>
      <v-card v-if="selectedAdjustment" rounded="lg">
        <!-- Dialog Header -->
        <v-card-title class="d-flex align-center gap-2 px-5 pt-5">
          <v-icon color="blue">mdi-clipboard-list-outline</v-icon>
          Detail Pengajuan
          <v-spacer />
          <v-chip
            :color="getStatus(selectedAdjustment.status).color"
            :prepend-icon="getStatus(selectedAdjustment.status).icon"
            variant="tonal"
            size="small">
            {{ getStatus(selectedAdjustment.status).label }}
          </v-chip>
        </v-card-title>

        <v-divider />

        <v-card-text class="pa-5">
          <!-- Meta info -->
          <v-row dense class="mb-4">
            <v-col cols="12" sm="6">
              <div class="text-caption text-grey-darken-1">Diajukan Oleh</div>
              <div class="text-body-2 font-weight-medium">{{ selectedAdjustment.created_by ?? '-' }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-grey-darken-1">Tanggal Pengajuan</div>
              <div class="text-body-2 font-weight-medium">{{ formatDate(selectedAdjustment.created_at) }}</div>
            </v-col>
            <v-col v-if="selectedAdjustment.notes" cols="12" class="mt-2">
              <div class="text-caption text-grey-darken-1">Catatan</div>
              <v-sheet rounded="lg" color="grey-lighten-4" class="pa-3 mt-1">
                <span class="text-body-2">{{ selectedAdjustment.notes }}</span>
              </v-sheet>
            </v-col>
          </v-row>

          <v-divider class="mb-4" />

          <!-- Tabel item -->
          <div class="text-body-2 font-weight-medium mb-3">Daftar item</div>
          <v-table density="compact">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Barang</th>
                <th class="text-center">Stok saat ini</th>
                <th class="text-center">Perubahan</th>
                <th class="text-center">Stok baru</th>
                <th>Alasan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in selectedAdjustment.items" :key="i">
                <td>{{ i + 1 }}</td>
                <td>{{ item.item?.name ?? '-' }}</td>
                <td class="text-center">
                  {{ item.old_stock }}
                  <span class="text-caption text-grey">{{ item.item?.unit?.name }}</span>
                </td>
                <td class="text-center">
                  <v-chip
                    :color="
                      item.new_stock - item.old_stock > 0
                        ? 'green'
                        : item.new_stock - item.old_stock < 0
                          ? 'red'
                          : 'grey'
                    "
                    variant="tonal"
                    size="x-small">
                    <v-icon start size="12">
                      {{
                        item.new_stock - item.old_stock > 0
                          ? 'mdi-arrow-up'
                          : item.new_stock - item.old_stock < 0
                            ? 'mdi-arrow-down'
                            : 'mdi-minus'
                      }}
                    </v-icon>
                    {{
                      item.new_stock - item.old_stock > 0
                        ? `+${item.new_stock - item.old_stock}`
                        : item.new_stock - item.old_stock
                    }}
                  </v-chip>
                </td>
                <td class="text-center">
                  {{ item.new_stock }}
                  <span class="text-caption text-grey">{{ item.item?.unit?.name }}</span>
                </td>
                <td>{{ item.reason?.reason ?? '-' }}</td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>

        <v-divider />

        <!-- Dialog Actions -->
        <v-card-actions class="px-5 py-4">
          <v-spacer />
          <v-btn variant="outlined" color="grey-darken-1" @click="detailDialog = false"> Tutup </v-btn>
          <!-- Tombol approve/reject hanya muncul jika canApprove dan masih pending -->
          <!-- Saat dummy: canApprove di-hardcode true supaya bisa dicoba -->
          <template v-if="(canApprove || props.adjustments.length === 0) && selectedAdjustment.status === 'pending'">
            <v-btn variant="flat" color="red" :disabled="processing" @click="askConfirm('reject', selectedAdjustment)">
              <v-icon icon="mdi-close" start />
              Tolak
            </v-btn>
            <v-btn
              variant="flat"
              color="green"
              :disabled="processing"
              @click="askConfirm('approve', selectedAdjustment)">
              <v-icon icon="mdi-check" start />
              Setujui
            </v-btn>
          </template>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Header -->
    <v-row class="mb-4">
      <v-col>
        <PageTitleHighlightPart first-part-title="Persetujuan" second-part-title="Penyesuaian Stok" />
      </v-col>
    </v-row>

    <!-- Filter & Search -->
    <v-row class="mb-2" align="center">
      <v-col cols="12" sm="5" md="4">
        <v-text-field
          v-model="search"
          placeholder="Cari pengaju atau catatan..."
          prepend-inner-icon="mdi-magnify"
          density="comfortable"
          variant="outlined"
          color="blue"
          clearable
          hide-details />
      </v-col>
      <v-col cols="12" sm="7" md="8">
        <v-btn-toggle v-model="statusFilter" density="comfortable" color="blue" variant="outlined" divided mandatory>
          <v-btn v-for="opt in statusOptions" :key="opt.value" :value="opt.value" size="small">
            {{ opt.title }}
          </v-btn>
        </v-btn-toggle>
      </v-col>
    </v-row>

    <!-- Tabel -->
    <v-row>
      <v-col>
        <v-card rounded="lg" elevation="0" border>
          <v-data-table
            :headers="headers"
            :items="filteredAdjustments"
            hover
            density="comfortable"
            no-data-text="Tidak ada pengajuan.">
            <template #item.index="{ index }">
              {{ index + 1 }}
            </template>

            <template #item.created_at="{ item }">
              {{ formatDate(item.created_at) }}
            </template>

            <template #item.created_by="{ item }">
              <div class="d-flex align-center gap-2">
                <v-avatar size="28" color="blue-lighten-4">
                  <span class="text-caption text-blue-darken-2 font-weight-bold">
                    {{ item.created_by?.charAt(0).toUpperCase() ?? '?' }}
                  </span>
                </v-avatar>
                {{ item.created_by ?? '-' }}
              </div>
            </template>

            <template #item.items_count="{ item }">
              <v-chip size="x-small" color="blue" variant="tonal"> {{ item.items?.length ?? 0 }} item </v-chip>
            </template>

            <template #item.notes="{ item }">
              <span class="text-grey-darken-1 text-body-2">
                {{ item.notes ? (item.notes.length > 40 ? item.notes.slice(0, 40) + '…' : item.notes) : '-' }}
              </span>
            </template>

            <template #item.status="{ item }">
              <v-chip
                :color="getStatus(item.status).color"
                :prepend-icon="getStatus(item.status).icon"
                variant="tonal"
                size="small">
                {{ getStatus(item.status).label }}
              </v-chip>
            </template>

            <template #item.actions="{ item }">
              <v-btn size="small" variant="tonal" color="blue" @click="openDetail(item)">
                <v-icon icon="mdi-eye-outline" start size="16" />
                Detail
              </v-btn>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

defineProps({
  opnames: {
    type: Array,
    default: () => [],
  },
});

const successDialog = ref(false);
const successMessage = ref('');
const errorDialog = ref(false);
const errorMessage = ref('');

const detailDialog = ref(false);
const selectedOpname = ref(null);

const rejectDialog = ref(false);
const rejectForm = useForm({
  opname_id: null,
  rejection_note: '',
});

const confirmDialog = ref(false);
const pendingApproveId = ref(null);

// --- Detail ---
function openDetail(opname) {
  selectedOpname.value = opname;
  detailDialog.value = true;
}

// --- Approve ---
function openConfirmApprove(id) {
  pendingApproveId.value = id;
  confirmDialog.value = true;
}

function confirmApprove() {
  router.patch(
    route('stock-opname.approve', pendingApproveId.value),
    {},
    {
      onSuccess: () => {
        confirmDialog.value = false;
        successMessage.value = 'Stock opname berhasil disetujui.';
        successDialog.value = true;
      },
      onError: (errors) => {
        confirmDialog.value = false;
        errorMessage.value = errors.error || errors.message || 'Terjadi kesalahan.';
        errorDialog.value = true;
      },
    },
  );
}

// --- Reject ---
function openReject(id) {
  rejectForm.opname_id = id;
  rejectForm.rejection_note = '';
  rejectDialog.value = true;
}

function submitReject() {
  rejectForm.patch(route('stock-opname.reject', rejectForm.opname_id), {
    onSuccess: () => {
      rejectDialog.value = false;
      successMessage.value = 'Stock opname berhasil ditolak.';
      successDialog.value = true;
    },
    onError: (errors) => {
      errorMessage.value = errors.error || errors.message || Object.values(errors)[0];
      errorDialog.value = true;
    },
  });
}

// --- Helpers ---
function statusColor(status) {
  if (status === 'approved') return 'success';
  if (status === 'rejected') return 'error';
  return 'warning';
}

function statusLabel(status) {
  if (status === 'approved') return 'Disetujui';
  if (status === 'rejected') return 'Ditolak';
  return 'Menunggu';
}

function getDiff(physical, system) {
  if (physical === null || physical === undefined) return null;
  return Number(physical) - Number(system);
}

function getDiffColor(diff) {
  if (diff === null) return 'default';
  if (diff > 0) return 'success';
  if (diff < 0) return 'error';
  return 'default';
}
</script>

<template>
  <Head>
    <title>Persetujuan Stock Opname</title>
  </Head>

  <v-container fluid class="pa-4 pa-md-6">
    <SuccessDialog v-model="successDialog" :message="successMessage" />
    <AlertDialog title="Gagal!" v-model="errorDialog" :message="errorMessage" />

    <!-- Header -->
    <v-row class="mb-4">
      <v-col>
        <PageTitleHighlightPart first-part-title="Persetujuan" second-part-title="Stock Opname" />
      </v-col>
    </v-row>

    <!-- Table -->
    <v-card variant="outlined" rounded="lg">
      <v-table density="comfortable" hover>
        <thead>
          <tr>
            <th class="text-caption text-uppercase font-weight-medium" style="width: 40px;">#</th>
            <th class="text-caption text-uppercase font-weight-medium">Tanggal</th>
            <th class="text-caption text-uppercase font-weight-medium">Dibuat Oleh</th>
            <th class="text-caption text-uppercase font-weight-medium">Jumlah Barang</th>
            <th class="text-caption text-uppercase font-weight-medium">Status</th>
            <th class="text-caption text-uppercase font-weight-medium" style="width: 160px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="opnames.length === 0">
            <td colspan="6" class="text-center text-body-2 text-grey py-8">
              Tidak ada data opname yang perlu disetujui.
            </td>
          </tr>
          <tr v-for="(opname, index) in opnames" :key="opname.id">
            <td class="text-caption text-grey-darken-1">{{ index + 1 }}</td>
            <td class="text-body-2">{{ opname.opname_date }}</td>
            <td class="text-body-2">{{ opname.created_by?.name ?? '—' }}</td>
            <td class="text-body-2">{{ opname.items?.length ?? 0 }} barang</td>
            <td>
              <v-chip
                :color="statusColor(opname.status)"
                size="small"
                variant="tonal"
                label>
                {{ statusLabel(opname.status) }}
              </v-chip>
            </td>
            <td>
              <div class="d-flex gap-1">
                <!-- Detail -->
                <v-btn
                  size="x-small"
                  variant="tonal"
                  color="blue"
                  icon
                  title="Lihat Detail"
                  @click="openDetail(opname)">
                  <v-icon size="15">mdi-eye</v-icon>
                </v-btn>

                <!-- Approve -->
                <v-btn
                  v-if="opname.status === 'pending'"
                  size="x-small"
                  variant="tonal"
                  color="success"
                  icon
                  title="Setujui"
                  @click="openConfirmApprove(opname.id)">
                  <v-icon size="15">mdi-check</v-icon>
                </v-btn>

                <!-- Reject -->
                <v-btn
                  v-if="opname.status === 'pending'"
                  size="x-small"
                  variant="tonal"
                  color="error"
                  icon
                  title="Tolak"
                  @click="openReject(opname.id)">
                  <v-icon size="15">mdi-close</v-icon>
                </v-btn>
              </div>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <!-- Detail Dialog -->
    <v-dialog v-model="detailDialog" max-width="800" scrollable>
      <v-card rounded="lg" v-if="selectedOpname">
        <v-card-title class="d-flex align-center justify-space-between pa-4 pb-2">
          <div>
            <span class="text-h6 font-weight-medium">Detail Stock Opname</span>
            <div class="text-caption text-grey-darken-1 mt-1">
              {{ selectedOpname.opname_date }} &bull; {{ selectedOpname.created_by?.name ?? '—' }}
            </div>
          </div>
          <v-chip
            :color="statusColor(selectedOpname.status)"
            size="small"
            variant="tonal"
            label>
            {{ statusLabel(selectedOpname.status) }}
          </v-chip>
        </v-card-title>

        <v-divider />

        <v-card-text class="pa-0">
          <v-table density="compact">
            <thead>
              <tr>
                <th class="text-caption text-uppercase">#</th>
                <th class="text-caption text-uppercase">Barang</th>
                <th class="text-caption text-uppercase">Stok Sistem</th>
                <th class="text-caption text-uppercase">Stok Fisik</th>
                <th class="text-caption text-uppercase">Selisih</th>
                <th class="text-caption text-uppercase">Sebab</th>
                <th class="text-caption text-uppercase">Deskripsi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in selectedOpname.items" :key="i">
                <td class="text-caption text-grey-darken-1">{{ i + 1 }}</td>
                <td class="text-body-2">{{ item.item?.name ?? '—' }}</td>
                <td class="text-body-2">{{ item.system_stock }}</td>
                <td class="text-body-2">{{ item.physical_stock ?? '—' }}</td>
                <td>
                  <v-chip
                    v-if="getDiff(item.physical_stock, item.system_stock) !== null"
                    :color="getDiffColor(getDiff(item.physical_stock, item.system_stock))"
                    size="x-small"
                    variant="tonal"
                    label>
                    {{ getDiff(item.physical_stock, item.system_stock) > 0 ? '+' : '' }}
                    {{ getDiff(item.physical_stock, item.system_stock) }}
                  </v-chip>
                  <span v-else class="text-caption text-grey">—</span>
                </td>
                <td class="text-body-2">{{ item.reason?.name ?? '—' }}</td>
                <td class="text-body-2 text-grey-darken-1">{{ item.remark ?? '—' }}</td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>

        <v-divider />

        <v-card-actions class="pa-4 justify-space-between">
          <!-- Approve/Reject from detail if still pending -->
          <div class="d-flex gap-2" v-if="selectedOpname.status === 'pending'">
            <v-btn
              variant="tonal"
              color="error"
              size="small"
              @click="detailDialog = false; openReject(selectedOpname.id)">
              <v-icon icon="mdi-close" start />
              Tolak
            </v-btn>
            <v-btn
              variant="flat"
              color="success"
              size="small"
              @click="detailDialog = false; openConfirmApprove(selectedOpname.id)">
              <v-icon icon="mdi-check" start />
              Setujui
            </v-btn>
          </div>
          <v-spacer v-else />
          <v-btn variant="outlined" color="grey-darken-1" size="small" @click="detailDialog = false">
            Tutup
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Confirm Approve Dialog -->
    <v-dialog v-model="confirmDialog" max-width="400">
      <v-card rounded="lg">
        <v-card-title class="text-h6 font-weight-medium pa-4 pb-2">Konfirmasi Persetujuan</v-card-title>
        <v-card-text class="text-body-2 text-grey-darken-2 px-4 py-2">
          Apakah kamu yakin ingin menyetujui stock opname ini? Stok barang akan diperbarui sesuai data fisik.
        </v-card-text>
        <v-card-actions class="pa-4 pt-2 justify-end gap-2">
          <v-btn variant="outlined" color="grey-darken-1" size="small" @click="confirmDialog = false">
            Batal
          </v-btn>
          <v-btn variant="flat" color="success" size="small" @click="confirmApprove">
            <v-icon icon="mdi-check" start />
            Ya, Setujui
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Reject Dialog -->
    <v-dialog v-model="rejectDialog" max-width="460">
      <v-card rounded="lg">
        <v-card-title class="text-h6 font-weight-medium pa-4 pb-2">Tolak Stock Opname</v-card-title>
        <v-card-text class="px-4 py-2">
          <v-textarea
            v-model="rejectForm.rejection_note"
            label="Alasan Penolakan"
            placeholder="Tuliskan alasan penolakan..."
            rows="3"
            density="comfortable"
            variant="outlined"
            color="blue"
            :error-messages="rejectForm.errors.rejection_note"
            auto-grow />
        </v-card-text>
        <v-card-actions class="pa-4 pt-0 justify-end gap-2">
          <v-btn
            variant="outlined"
            color="grey-darken-1"
            size="small"
            :disabled="rejectForm.processing"
            @click="rejectDialog = false">
            Batal
          </v-btn>
          <v-btn
            variant="flat"
            color="error"
            size="small"
            :loading="rejectForm.processing"
            :disabled="rejectForm.processing"
            @click="submitReject">
            <v-icon icon="mdi-close" start />
            Tolak
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-container>
</template>
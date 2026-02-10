<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { canAcceptItemRequest, canDeleteItemRequest, canPrintItemRequest, canRejectItemRequest } from '@/lib/can';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const {
  auth,
  itemRequests: itemRequestsProp,
  filters,
  settings
} = defineProps({
  auth: {
    type: Object,
    default: null,
  },
  itemRequests: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  settings: {
    type: Object,
    default: () => ({}),
  }
});

const currentUser = auth?.user;

// Ambil data dari prop itemRequests (Laravel pagination object)
const itemRequests = computed(() => itemRequestsProp.data);
const totalItems = computed(() => itemRequestsProp.total);
const itemsPerPage = computed(() => itemRequestsProp.per_page);
const currentPage = computed(() => itemRequestsProp.current_page);

const loading = ref(false);
const detailDialog = ref(false);
const rejectDialog = ref(false);
const approveDialog = ref(false);
const editDialog = ref(false);
const selectedRequest = ref(null);
const rejectNotes = ref('');
const approvalData = ref({
  items: [],
  notes: '',
});
const editData = ref({
  items: [],
});

// Search filters
const search = ref(filters.search || '');
const statusFilter = ref(filters.status || '');

// Watch search dengan debounce
let searchTimeout = null;
watch([search, statusFilter], () => {
  if (searchTimeout) clearTimeout(searchTimeout);

  searchTimeout = setTimeout(() => {
    loadItems({
      page: 1,
      itemsPerPage: itemsPerPage.value,
    });
  }, 300);
});

function loadItems({ page, itemsPerPage: perPage }) {
  loading.value = true;

  router.get(
    route('items.requests'),
    {
      page,
      per_page: perPage,
      search: search.value || undefined,
      status: statusFilter.value || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        loading.value = false;
      },
    },
  );
}

function resetFilters() {
  search.value = '';
  statusFilter.value = '';
}

async function deleteItemRequest(id) {
  router.delete(route('items.requests.delete', id), { preserveScroll: true });
}

function showDetail(request) {
  selectedRequest.value = request;
  detailDialog.value = true;
}

function showRejectDialog(request) {
  selectedRequest.value = request;
  rejectNotes.value = '';
  rejectDialog.value = true;
}

function showApproveDialog(request) {
  selectedRequest.value = request;
  // Initialize approval data dengan jumlah yang diminta
  approvalData.value.items = request.item_request_details.map((detail) => ({
    id: detail.id,
    item_name: detail.item?.name,
    specification: detail.item?.spesification_name,
    unit: detail.item?.unit?.name,
    requested_quantity: detail.requested_quantity,
    received_quantity: detail.requested_quantity, // Default sama dengan yang diminta
  }));
  approvalData.value.notes = '';
  approveDialog.value = true;
}

function showEditDialog(request) {
  selectedRequest.value = request;
  // Initialize edit data
  editData.value.items = request.item_request_details.map((detail) => ({
    id: detail.id,
    item_id: detail.item_id,
    item_name: detail.item?.name,
    specification: detail.item?.spesification_name,
    unit: detail.item?.unit?.name,
    requested_quantity: detail.requested_quantity,
    price: detail.price,
  }));
  editDialog.value = true;
}

function handleReject() {
  if (!rejectNotes.value.trim()) {
    alert('Catatan penolakan harus diisi');
    return;
  }

  router.put(
    route('items.requests.reject', selectedRequest.value.id),
    {
      responder_notes: rejectNotes.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        rejectDialog.value = false;
        rejectNotes.value = '';
      },
    },
  );
}

function handleAccept() {
  // Validasi jumlah yang diterima
  const hasInvalidQuantity = approvalData.value.items.some((item) => item.received_quantity < 0 || item.received_quantity > item.requested_quantity);

  if (hasInvalidQuantity) {
    alert('Jumlah yang diterima tidak boleh negatif atau melebihi jumlah yang diminta');
    return;
  }

  router.put(
    route('items.requests.accept', selectedRequest.value.id),
    {
      responder_notes: approvalData.value.notes,
      items: approvalData.value.items.map((item) => ({
        id: item.id,
        received_quantity: item.received_quantity,
      })),
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        approveDialog.value = false;
      },
      onError: (e) => {
        if (e.error) {
          alert(e.error);
        }
        // Jika error berupa validation errors
        else if (e.errors) {
          const errorMessages = Object.values(e.errors).flat().join('\n');
          alert(errorMessages);
        }
        // Jika error berupa message
        else if (e.message) {
          alert(e.message);
        }
        // Fallback
        else {
          alert('Terjadi kesalahan, silakan coba lagi');
        }
      },
    },
  );
}

function handleUpdate() {
  // Validasi jumlah yang diminta
  const hasInvalidQuantity = editData.value.items.some((item) => !item.requested_quantity || item.requested_quantity <= 0);

  if (hasInvalidQuantity) {
    alert('Jumlah yang diminta harus lebih dari 0');
    return;
  }

  router.put(
    route('items.requests.update', selectedRequest.value.id),
    {
      items: editData.value.items.map((item) => ({
        id: item.id,
        item_id: item.item_id,
        requested_quantity: item.requested_quantity,
      })),
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        editDialog.value = false;
      },
    },
  );
}

function removeItemFromEdit(index) {
  if (editData.value.items.length > 1) {
    editData.value.items.splice(index, 1);
  } else {
    alert('Minimal harus ada 1 item dalam permintaan');
  }
}

function printRequest(request) {
  // Simpan request yang akan dicetak
  const printContent = generatePrintContent(request);

  // Buka window baru untuk print
  const printWindow = window.open('', '_blank');
  printWindow.document.write(printContent);
  printWindow.document.close();
  printWindow.focus();

  // Trigger print setelah konten dimuat
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 250);
}

function generatePrintContent(request) {
  const totalValue = getTotalValue(request);
  const totalItems = getTotalItems(request);

  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Permintaan Barang - ${formatDate(request.request_date)}</title>
      <style>
        @page {
          size: A5;
          margin: 10mm;
        }
        body {
          font-family: Arial, sans-serif;
          padding: 0;
          margin: 0;
          font-size: 11px;
        }
        .header {
          text-align: center;
          margin-bottom: 15px;
          border-bottom: 2px solid #333;
          padding-bottom: 10px;
          display: flex;
          padding-left:8px;
          padding-right:8px;
          justify-content:space-between;
          align-items: baseline;
        }
        .header > img {
          filter: grayscale(100%);
          transform: translateY(10px);
        }
        .header h1 {
          margin: 0;
          font-size: 16px;
        }
        .info-section {
          margin-bottom: 15px;
        }
        .info-row {
          display: flex;
          margin-bottom: 4px;
        }
        .info-label {
          width: 120px;
          font-weight: bold;
          font-size: 10px;
        }
        .info-value {
          flex: 1;
          font-size: 10px;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin-top: 10px;
        }
        th, td {
          border: 1px solid #333;
          padding: 4px;
          text-align: left;
          font-size: 10px;
        }
        th {
          background-color: #f0f0f0;
          font-weight: bold;
        }
        .text-right {
          text-align: right;
        }
        .text-center {
          text-align: center;
        }
        @media print {
          body { 
            padding: 0;
            margin: 0;
          }
        }
      </style>
    </head>
    <body>
      <div class="header">
        <img src="${settings.icon}" style="width: 50px;height:auto"/>
        <h1>PERMINTAAN BARANG</h1>
        <img src="${settings.icon}" style="width: 50px;height:auto"/>
      </div>
      
      <div class="info-section">
        <div class="info-row">
          <div class="info-label">Tanggal Permintaan:</div>
          <div class="info-value">${formatDate(request.request_date)}</div>
        </div>
        <div class="info-row">
          <div class="info-label">Dipinta Oleh :</div>
          <div class="info-value">${request.requester?.name || '-'}</div>
        </div>
        ${
          request.responder
            ? `
        <div class="info-row">
          <div class="info-label">Penerima Permintaan:</div>
          <div class="info-value">${request.responder.name}</div>
        </div>
        `
            : ''
        }
        ${
          request.response_date
            ? `
        <div class="info-row">
          <div class="info-label">Tanggal Diterima/Ditolak:</div>
          <div class="info-value">${formatDate(request.response_date)}</div>
        </div>
        `
            : ''
        }
        ${
          request.responder_notes
            ? `
        <div class="info-row">
          <div class="info-label">Catatan:</div>
          <div class="info-value">${request.responder_notes}</div>
        </div>
        `
            : ''
        }
      </div>
      
      <table>
        <thead>
          <tr>
            <th class="text-center" style="width: 30px;">No</th>
            <th>Nama Barang</th>
            <th>Nama Spesifikasi</th>
            <th class="text-right" style="width: 80px;">Diminta</th>
            ${request.status !== 'pending' ? '<th class="text-right" style="width: 80px;">Diterima</th>' : ''}
          </tr>
        </thead>
        <tbody>
          ${request.item_request_details
            .map(
              (detail, index) => `
            <tr>
              <td class="text-center">${index + 1}</td>
              <td>${detail.item?.name || '-'}</td>
              <td>${detail.item?.spesification_name || '-'}</td>
              <td class="text-right">${detail.requested_quantity} ${detail.item?.unit?.name || ''}</td>
              ${request.status !== 'pending' ? `<td class="text-right">${detail.received_quantity} ${detail.item?.unit?.name || ''}</td>` : ''}
            </tr>
          `,
            )
            .join('')}
        </tbody>
      </table>
    </body>
    </html>
  `;
}

function getStatusColor(status) {
  const colors = {
    pending: 'blue-grey-lighten-4',
    accepted: 'green-accent-2',
    rejected: 'red-accent-1',
  };
  return colors[status] || 'grey';
}

function getStatusText(status) {
  const texts = {
    pending: 'Menunggu',
    accepted: 'Diterima',
    rejected: 'Ditolak',
  };
  return texts[status] || status;
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  });
}

function formatRupiah(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value);
}

function getTotalItems(request) {
  return request.item_request_details?.reduce((sum, detail) => sum + detail.requested_quantity, 0) || 0;
}

function getTotalValue(request) {
  return request.item_request_details?.reduce((sum, detail) => sum + detail.requested_quantity * detail.price, 0) || 0;
}

function disableDeleteButton(request) {
  return request.status !== 'pending';
}

function canEdit(request) {
  return request.status === 'pending' && request.requester_id === currentUser?.id;
}

function canApproveOrReject(request) {
  // Sesuaikan dengan logic authorization Anda
  // Misal: hanya admin/gudang yang bisa approve/reject
  return request.status === 'pending' && currentUser?.role?.name !== 'ruangan';
}
</script>

<template>
  <Head title="Permintaan Barang"></Head>
  <v-container>
    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Daftar Permintaan"
          second-part-title="Barang"
        />
      </v-col>
    </v-row>

    <!-- Filter Section -->
    <v-row>
      <v-col
        cols="12"
        md="6"
      >
        <v-text-field
          v-model="search"
          label="Cari permintaan..."
          placeholder="Cari berdasarkan dipinta oleh, Penerima Permintaan..."
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          hide-details
        />
      </v-col>
      <v-col
        cols="12"
        md="4"
      >
        <v-select
          v-model="statusFilter"
          label="Filter Status"
          :items="[
            { title: 'Semua Status', value: '' },
            { title: 'Menunggu', value: 'pending' },
            { title: 'Diterima', value: 'accepted' },
            { title: 'Ditolak', value: 'rejected' },
          ]"
          variant="outlined"
          density="compact"
          clearable
          hide-details
        />
      </v-col>
      <v-col
        cols="12"
        md="2"
        class="d-flex align-center"
      >
        <v-btn
          color="grey"
          variant="outlined"
          block
          @click="resetFilters"
        >
          <v-icon start>mdi-refresh</v-icon>
          Reset
        </v-btn>
      </v-col>
    </v-row>

    <!-- Desktop Table View -->
    <v-row class="hidden! md:block!">
      <v-col>
        <v-data-table-server
          :headers="[
            { title: 'No', key: 'no' },
            { title: 'Tanggal Permintaan', key: 'request_date' },
            { title: 'Dipinta Oleh', key: 'requester.name' },
            { title: 'Total Item', key: 'total_items' },
            { title: 'Status', key: 'status' },
            { title: 'Penerima Permintaan', key: 'responder.name' },
            { title: 'Tanggal Diterima/Ditolak', key: 'response_date' },
            { title: 'Tindakan', key: 'actions' },
          ]"
          :items="itemRequests"
          :items-length="totalItems"
          :loading="loading"
          :items-per-page="itemsPerPage"
          :page="currentPage"
          class="hidden! md:block!"
          @update:options="loadItems"
        >
          <template #headers="{ headers }">
            <tr class="bg-blue-darken-2">
              <th class="w-1/16">No</th>
              <th>Tanggal Permintaan</th>
              <th>Dipinta Oleh</th>
              <th>Total Item</th>
              <th>Status</th>
              <th>Penerima Permintaan</th>
              <th>Tanggal Diterima/Ditolak</th>
              <th class="w-1/12">Tindakan</th>
            </tr>
          </template>
          <template #item.no="{ index }">
            {{ (currentPage - 1) * itemsPerPage + index + 1 }}
          </template>
          <template #item.request_date="{ item }">
            {{ formatDate(item.request_date) }}
          </template>
          <template #item.total_items="{ item }"> {{ getTotalItems(item) }} item </template>
          <template #item.status="{ item }">
            <v-chip
              :color="getStatusColor(item.status)"
              size="small"
              variant="flat"
            >
              {{ getStatusText(item.status) }}
            </v-chip>
          </template>
          <template #item.responder.name="{ item }">
            {{ item.responder?.name || '-' }}
          </template>
          <template #item.response_date="{ item }">
            {{ formatDate(item.response_date) }}
          </template>
          <template #item.actions="{ item }">
            <v-btn
              variant="text"
              icon
            >
              <v-icon icon="mdi-dots-vertical" />
              <v-menu activator="parent">
                <v-list density="compact">
                  <v-list-item
                    value="view"
                    @click="showDetail(item)"
                  >
                    <v-icon
                      icon="mdi-eye"
                      class="mr-2"
                    />
                    Detail
                  </v-list-item>

                  <v-list-item
                    v-if="canPrintItemRequest($page?.props?.auth?.user, item)"
                    value="print"
                    @click="printRequest(item)"
                  >
                    <v-icon
                      icon="mdi-printer"
                      class="mr-2"
                    />
                    Cetak
                  </v-list-item>

                  <v-list-item
                    v-if="canEdit(item)"
                    value="edit"
                    @click="showEditDialog(item)"
                  >
                    <v-icon
                      icon="mdi-pencil"
                      class="mr-2"
                      color="blue"
                    />
                    Edit
                  </v-list-item>

                  <v-list-item
                    v-if="canAcceptItemRequest($page?.props?.auth?.user)"
                    value="approve"
                    @click="showApproveDialog(item)"
                  >
                    <v-icon
                      icon="mdi-check-circle"
                      class="mr-2"
                      color="green"
                    />
                    Terima
                  </v-list-item>

                  <v-list-item
                    v-if="canRejectItemRequest($page?.props?.auth?.user)"
                    value="reject"
                    @click="showRejectDialog(item)"
                  >
                    <v-icon
                      icon="mdi-close-circle"
                      class="mr-2"
                      color="red"
                    />
                    Tolak
                  </v-list-item>

                  <v-list-item
                    value="delete"
                    v-if="canDeleteItemRequest($page?.props?.auth?.user, item)"
                    :disabled="disableDeleteButton(item)"
                  >
                    <v-icon
                      icon="mdi-delete"
                      class="mr-2"
                    />
                    Hapus
                    <v-dialog
                      v-slot="{ isActive }"
                      activator="parent"
                      max-width="400"
                    >
                      <v-card>
                        <v-card-title class="bg-blue-darken-2 text-center text-wrap"> Konfirmasi! </v-card-title>
                        <v-card-text>
                          <div>
                            Apakah yakin untuk menghapus permintaan barang tanggal
                            <span class="text-blue-600">{{ formatDate(item.request_date) }}</span
                            >?
                          </div>
                        </v-card-text>
                        <v-card-actions>
                          <v-btn
                            @click="
                              deleteItemRequest(item.id);
                              isActive.value = false;
                            "
                          >
                            Ya
                          </v-btn>
                          <v-btn @click="isActive.value = false">Batal</v-btn>
                        </v-card-actions>
                      </v-card>
                    </v-dialog>
                  </v-list-item>
                </v-list>
              </v-menu>
            </v-btn>
          </template>
        </v-data-table-server>
      </v-col>
    </v-row>

    <!-- Mobile Card View -->
    <v-row class="md:hidden!">
      <v-col>
        <v-progress-circular
          v-if="loading"
          indeterminate
          class="d-block mx-auto my-4"
        />
        <v-row
          v-for="(request, index) in itemRequests"
          :key="request.id"
        >
          <v-col>
            <v-card>
              <v-card-actions class="bg-blue-darken-2 flex justify-end">
                <v-btn
                  variant="text"
                  icon
                >
                  <v-icon icon="mdi-dots-vertical" />
                  <v-menu activator="parent">
                    <v-list density="compact">
                      <v-list-item
                        value="view"
                        @click="showDetail(request)"
                      >
                        <v-icon
                          icon="mdi-eye"
                          class="mr-2"
                        />
                        Detail
                      </v-list-item>

                      <v-list-item
                        v-if="canPrintItemRequest($page?.props?.auth?.user, request)"
                        value="print"
                        @click="printRequest(request)"
                      >
                        <v-icon
                          icon="mdi-printer"
                          class="mr-2"
                        />
                        Cetak
                      </v-list-item>

                      <v-list-item
                        v-if="canEdit(request)"
                        value="edit"
                        @click="showEditDialog(request)"
                      >
                        <v-icon
                          icon="mdi-pencil"
                          class="mr-2"
                          color="blue"
                        />
                        Edit
                      </v-list-item>

                      <v-list-item
                        v-if="canAcceptItemRequest($page?.props?.auth?.user)"
                        value="approve"
                        @click="showApproveDialog(request)"
                      >
                        <v-icon
                          icon="mdi-check-circle"
                          class="mr-2"
                          color="green"
                        />
                        Terima
                      </v-list-item>

                      <v-list-item
                        v-if="canRejectItemRequest($page?.props?.auth?.user)"
                        value="reject"
                        @click="showRejectDialog(request)"
                      >
                        <v-icon
                          icon="mdi-close-circle"
                          class="mr-2"
                          color="red"
                        />
                        Tolak
                      </v-list-item>

                      <v-list-item
                        value="delete"
                        v-if="canDeleteItemRequest($page?.props?.auth?.user, request)"
                        :disabled="disableDeleteButton(request)"
                      >
                        <v-icon
                          icon="mdi-delete"
                          class="mr-2"
                        />
                        Hapus
                        <v-dialog
                          v-slot="{ isActive }"
                          activator="parent"
                          max-width="400"
                        >
                          <v-card>
                            <v-card-title class="bg-blue-darken-2 text-center text-wrap"> Konfirmasi! </v-card-title>
                            <v-card-text>
                              <div>
                                Apakah yakin untuk menghapus permintaan barang tanggal
                                <span class="text-blue-600">{{ formatDate(request.request_date) }}</span
                                >?
                              </div>
                            </v-card-text>
                            <v-card-actions>
                              <v-btn
                                @click="
                                  deleteItemRequest(request.id);
                                  isActive.value = false;
                                "
                              >
                                Ya
                              </v-btn>
                              <v-btn @click="isActive.value = false">Batal</v-btn>
                            </v-card-actions>
                          </v-card>
                        </v-dialog>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </v-btn>
              </v-card-actions>
              <v-card-text>
                <v-row>
                  <v-col cols="5">No</v-col>
                  <v-col>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Tanggal Permintaan</v-col>
                  <v-col>{{ formatDate(request.request_date) }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Dipinta Oleh</v-col>
                  <v-col>{{ request.requester?.name }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Total Item</v-col>
                  <v-col>{{ getTotalItems(request) }} item</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Status</v-col>
                  <v-col>
                    <v-chip
                      :color="getStatusColor(request.status)"
                      size="small"
                      variant="flat"
                    >
                      {{ getStatusText(request.status) }}
                    </v-chip>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Penerima Permintaan</v-col>
                  <v-col>{{ request.responder?.name || '-' }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Tanggal Diterima/Ditolak</v-col>
                  <v-col>{{ formatDate(request.response_date) }}</v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Mobile Pagination -->
        <v-row v-if="!loading && itemRequests.length > 0">
          <v-col>
            <v-pagination
              :model-value="currentPage"
              :length="Math.ceil(totalItems / itemsPerPage)"
              total-visible="5"
              @update:model-value="(page) => loadItems({ page, itemsPerPage })"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Detail Dialog -->
    <v-dialog
      v-model="detailDialog"
      max-width="900"
      scrollable
    >
      <v-card v-if="selectedRequest">
        <v-card-title class="bg-blue-darken-2 d-flex align-center justify-space-between">
          <span>Detail Permintaan Barang</span>
          <v-btn
            icon
            variant="text"
            @click="detailDialog = false"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <!-- Info Permintaan -->
          <v-row>
            <v-col
              cols="12"
              md="6"
            >
              <div class="text-caption text-grey">Tanggal Permintaan</div>
              <div class="text-body-1 font-weight-medium">{{ formatDate(selectedRequest.request_date) }}</div>
            </v-col>
            <v-col
              cols="12"
              md="6"
            >
              <div class="text-caption text-grey">Status</div>
              <div>
                <v-chip
                  :color="getStatusColor(selectedRequest.status)"
                  size="small"
                  variant="flat"
                >
                  {{ getStatusText(selectedRequest.status) }}
                </v-chip>
              </div>
            </v-col>
          </v-row>

          <v-row>
            <v-col
              cols="12"
              md="6"
            >
              <div class="text-caption text-grey">Dipinta Oleh</div>
              <div class="text-body-1 font-weight-medium">{{ selectedRequest.requester?.name }}</div>
            </v-col>
            <v-col
              cols="12"
              md="6"
            >
              <div class="text-caption text-grey">Penerima Permintaan</div>
              <div class="text-body-1 font-weight-medium">{{ selectedRequest.responder?.name || '-' }}</div>
            </v-col>
          </v-row>

          <v-row v-if="selectedRequest.response_date">
            <v-col
              cols="12"
              md="6"
            >
              <div class="text-caption text-grey">Tanggal Diterima/Ditolak</div>
              <div class="text-body-1 font-weight-medium">{{ formatDate(selectedRequest.response_date) }}</div>
            </v-col>
          </v-row>

          <v-row v-if="selectedRequest.responder_notes">
            <v-col cols="12">
              <div class="text-caption text-grey">Catatan Penerima Permintaan</div>
              <div class="text-body-1">{{ selectedRequest.responder_notes }}</div>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <!-- Detail Barang -->
          <div class="text-h6 mb-3">Detail Barang</div>

          <v-table density="comfortable">
            <thead>
              <tr class="bg-grey-lighten-3">
                <th>No</th>
                <th>Nama Barang</th>
                <th>Nama Spesifikasi</th>
                <th class="text-right">Jumlah Diminta</th>
                <th
                  class="text-right"
                  v-if="selectedRequest.status !== 'pending'"
                >
                  Jumlah Diterima
                </th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(detail, index) in selectedRequest.item_request_details"
                :key="detail.id"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ detail.item?.name }}</td>
                <td>{{ detail.item?.spesification_name }}</td>
                <td class="text-right">{{ detail.requested_quantity }} {{ detail.item?.unit?.name }}</td>
                <td
                  class="text-right"
                  v-if="selectedRequest.status !== 'pending'"
                >
                  {{ detail.received_quantity }} {{ detail.item?.unit?.name }}
                </td>
                <td class="text-right">{{ formatRupiah(detail.price) }}</td>
                <td class="text-right">{{ formatRupiah(detail.requested_quantity * detail.price) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="bg-grey-lighten-2 font-weight-bold">
                <td
                  :colspan="selectedRequest.status !== 'pending' ? 6 : 5"
                  class="text-right"
                >
                  Total
                </td>
                <td class="text-right">{{ formatRupiah(getTotalValue(selectedRequest)) }}</td>
              </tr>
            </tfoot>
          </v-table>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            variant="tonal"
            color="grey"
            @click="detailDialog = false"
          >
            Tutup
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Reject Dialog -->
    <v-dialog
      v-model="rejectDialog"
      max-width="500"
    >
      <v-card>
        <v-card-title class="bg-red-darken-2 text-center"> Tolak Permintaan Barang </v-card-title>

        <v-card-text class="pt-4">
          <div class="mb-4"><strong>Tanggal Permintaan:</strong> {{ formatDate(selectedRequest?.request_date) }}</div>
          <div class="mb-4"><strong>Dipinta Oleh:</strong> {{ selectedRequest?.requester?.name }}</div>

          <v-textarea
            v-model="rejectNotes"
            label="Catatan Penolakan *"
            placeholder="Masukkan alasan penolakan..."
            rows="4"
            variant="outlined"
            required
          />
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            variant="tonal"
            color="grey"
            @click="rejectDialog = false"
          >
            Batal
          </v-btn>
          <v-btn
            color="red"
            @click="handleReject"
          >
            Tolak Permintaan
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Approve Dialog -->
    <v-dialog
      v-model="approveDialog"
      max-width="800"
      scrollable
    >
      <v-card>
        <v-card-title class="bg-green-darken-2 text-center"> Terima Permintaan Barang </v-card-title>

        <v-card-text class="pt-4">
          <div class="mb-4"><strong>Tanggal Permintaan:</strong> {{ formatDate(selectedRequest?.request_date) }}</div>
          <div class="mb-4"><strong>Dipinta Oleh:</strong> {{ selectedRequest?.requester?.name }}</div>

          <v-divider class="my-4"></v-divider>

          <div class="text-subtitle-1 font-weight-bold mb-3">Tentukan Jumlah yang Diterima</div>

          <v-table density="comfortable">
            <thead>
              <tr class="bg-grey-lighten-3">
                <th>No</th>
                <th>Nama Barang</th>
                <th>Nama Spesifikasi</th>
                <th class="text-right">Diminta</th>
                <th
                  class="text-right"
                  style="width: 150px"
                >
                  Diterima
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, index) in approvalData.items"
                :key="item.id"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ item.item_name }}</td>
                <td>{{ item.specification }}</td>
                <td class="text-right">{{ item.requested_quantity }} {{ item.unit }}</td>
                <td>
                  <v-text-field
                    v-model.number="item.received_quantity"
                    type="number"
                    :min="0"
                    :max="item.requested_quantity"
                    density="compact"
                    variant="outlined"
                    hide-details
                    :suffix="item.unit"
                  />
                </td>
              </tr>
            </tbody>
          </v-table>

          <v-divider class="my-4"></v-divider>

          <v-textarea
            v-model="approvalData.notes"
            label="Catatan (Opsional)"
            placeholder="Tambahkan catatan jika diperlukan..."
            rows="3"
            variant="outlined"
          />
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            variant="tonal"
            color="grey"
            @click="approveDialog = false"
          >
            Batal
          </v-btn>
          <v-btn
            color="green"
            @click="handleAccept"
          >
            Terima Permintaan
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Edit Dialog -->
    <v-dialog
      v-model="editDialog"
      max-width="900"
      scrollable
    >
      <v-card>
        <v-card-title class="bg-blue-darken-2 text-center"> Edit Permintaan Barang </v-card-title>

        <v-card-text class="pt-4">
          <div class="mb-4"><strong>Tanggal Permintaan:</strong> {{ formatDate(selectedRequest?.request_date) }}</div>
          <div class="mb-4"><strong>Dipinta Oleh:</strong> {{ selectedRequest?.requester?.name }}</div>

          <v-divider class="my-4"></v-divider>

          <div class="text-subtitle-1 font-weight-bold mb-3">Edit Jumlah Permintaan</div>

          <v-table density="comfortable">
            <thead>
              <tr class="bg-grey-lighten-3">
                <th>No</th>
                <th>Nama Barang</th>
                <th>Nama Spesifikasi</th>
                <th
                  class="text-right"
                  style="width: 180px"
                >
                  Jumlah Diminta
                </th>
                <th style="width: 80px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, index) in editData.items"
                :key="item.id"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ item.item_name }}</td>
                <td>{{ item.specification }}</td>
                <td>
                  <v-text-field
                    v-model.number="item.requested_quantity"
                    type="number"
                    :min="1"
                    density="compact"
                    variant="outlined"
                    hide-details
                    :suffix="item.unit"
                  />
                </td>
                <td class="text-center">
                  <v-btn
                    icon
                    size="small"
                    color="red"
                    variant="text"
                    @click="removeItemFromEdit(index)"
                    :disabled="editData.items.length === 1"
                  >
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            variant="tonal"
            color="grey"
            @click="editDialog = false"
          >
            Batal
          </v-btn>
          <v-btn
            color="blue"
            @click="handleUpdate"
          >
            Simpan Perubahan
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

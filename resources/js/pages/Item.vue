<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { canActItem } from '@/lib/can';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  items: {
    type: Object,
    required: true,
  },
  units: {
    type: [Array, null],
    default: [],
  },
});

const dialog = ref(false);
const editingId = ref(null);
const form = useForm({
  name: '',
  unit_id: '',
  spesification_name: '',
  stock: 0,
  price: 0,
});

const errorDialog = ref(false);
const errorMessage = ref('');
const errorTitle = ref('Gagal!');

// Pagination
const itemsData = computed(() => props.items.data || []);
const totalItems = computed(() => props.items.total || 0);
const itemsPerPage = computed(() => props.items.per_page || 10);
const currentPage = computed(() => props.items.current_page || 1);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));

const changePage = (page) => {
  router.get(
    route('items'),
    { page },
    {
      preserveState: true,
      preserveScroll: true,
    },
  );
};
if (currentPage.value > props.items.last_page) {
  changePage(1);
}

const openAddDialog = () => {
  editingId.value = null;
  form.reset();
  form.clearErrors();
  dialog.value = true;
};

const openEditDialog = (item) => {
  editingId.value = item.id;
  form.name = item.name;
  form.unit_id = item.unit_id;
  form.spesification_name = item.spesification_name;
  form.stock = item.stock;
  form.price = item.price;
  form.clearErrors();
  dialog.value = true;
};

const submitForm = () => {
  if (editingId.value) {
    form.put(route('items.update', editingId.value), {
      onSuccess: () => {
        dialog.value = false;
        form.reset();
        router.reload();
      },
      onError: (errors) => {
        // Cek apakah ada error non-field
        const fieldErrors = ['name', 'unit_id', 'spesification_name', 'stock', 'price'];
        const hasNonFieldError = Object.keys(errors).some((key) => !fieldErrors.includes(key));

        if (hasNonFieldError || errors.message) {
          errorTitle.value = 'Gagal Memperbarui!';
          errorMessage.value = errors.message || 'Terjadi kesalahan saat memperbarui barang.';
          errorDialog.value = true;
        }
      },
    });
  } else {
    form.post(route('items.create'), {
      onSuccess: () => {
        dialog.value = false;
        form.reset();
        router.reload();
      },
      onError: (errors) => {
        // Cek apakah ada error non-field
        const fieldErrors = ['name', 'unit_id', 'spesification_name', 'stock', 'price'];
        const hasNonFieldError = Object.keys(errors).some((key) => !fieldErrors.includes(key));

        if (hasNonFieldError || errors.message) {
          errorTitle.value = 'Gagal Menyimpan!';
          errorMessage.value = errors.message || 'Terjadi kesalahan saat menyimpan barang.';
          errorDialog.value = true;
        }
      },
    });
  }
};

const deleteItem = (id) => {
  form.delete(route('items.delete', id), {
    onSuccess: () => {
      router.reload();
    },
    onError: (errors) => {
      errorTitle.value = 'Gagal Menghapus!';
      errorMessage.value = errors.message || 'Terjadi kesalahan saat menghapus barang.';
      errorDialog.value = true;
    },
  });
};

const closeDialog = () => {
  dialog.value = false;
  form.reset();
  form.clearErrors();
};

// Format harga ke Rupiah
const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value);
};

// Get item number based on pagination
const getItemNumber = (index) => {
  return (currentPage.value - 1) * itemsPerPage.value + index + 1;
};
</script>

<template>
  <Head>
    <title v-if="canActItem($page.props.auth.user)">Data barang</title>
    <title v-else>Daftar barang</title>
  </Head>
  <v-container>
    <!-- Alert Dialog untuk error non-field -->
    <AlertDialog
      v-model="errorDialog"
      :title="errorTitle"
      :message="errorMessage"
    />

    <v-row>
      <v-col>
        <PageTitleHighlightPart
          :first-part-title="canActItem($page.props.auth.user) ? 'Data' : 'Daftar'"
          second-part-title="Barang"
        />
      </v-col>
    </v-row>

    <v-row v-if="canActItem($page.props.auth.user)">
      <v-col>
        <v-btn
          variant="tonal"
          color="primary"
          prepend-icon="mdi-package-variant-plus"
          @click="openAddDialog"
        >
          Tambah Barang
        </v-btn>
      </v-col>
    </v-row>

    <!-- Desktop Table View -->
    <v-row class="hidden! md:block!">
      <v-col cols="12">
        <v-table class="borderless-table">
          <thead class="bg-blue-darken-2">
            <tr>
              <th class="w-1/16 text-left">No</th>
              <th class="text-left">Nama Barang</th>
              <th class="text-left">Satuan</th>
              <th class="text-left">Spesifikasi</th>
              <th class="text-left">Stok</th>
              <th
                v-if="canActItem($page.props.auth.user)"
                class="text-left"
              >
                Harga
              </th>
              <th
                v-if="canActItem($page.props.auth.user)"
                class="w-1/12 text-left"
              >
                Tindakan
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in itemsData"
              :key="item.id"
            >
              <td>{{ getItemNumber(index) }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.unit.name }}</td>
              <td>{{ item.spesification_name }}</td>
              <td>{{ item.stock }}</td>
              <td v-if="canActItem($page.props.auth.user)">{{ formatRupiah(item.price) }}</td>
              <td v-if="canActItem($page.props.auth.user)">
                <v-btn
                  size="small"
                  icon="mdi-dots-vertical"
                  variant="text"
                ></v-btn>
                <v-menu activator="parent">
                  <v-list density="compact">
                    <v-list-item
                      value="edit"
                      @click="openEditDialog(item)"
                    >
                      <v-icon
                        icon="mdi-pencil"
                        class="mr-2"
                      />
                      Edit
                    </v-list-item>
                    <v-list-item value="delete">
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
                          <v-card-title class="bg-blue-darken-2 text-center text-wrap">Konfirmasi!</v-card-title>
                          <v-card-text>
                            <div>
                              Apakah Anda yakin ingin menghapus barang <span class="font-weight-bold text-blue-600">{{ item.name }}</span
                              >?
                            </div>
                          </v-card-text>
                          <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn @click="isActive.value = false">Batal</v-btn>
                            <v-btn
                              color="error"
                              @click="
                                deleteItem(item.id);
                                isActive.value = false;
                              "
                            >
                              Hapus
                            </v-btn>
                          </v-card-actions>
                        </v-card>
                      </v-dialog>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </td>
            </tr>
            <tr v-if="!itemsData || itemsData.length === 0">
              <td
                colspan="7"
                class="text-grey text-center"
              >
                Belum ada barang yang ditambahkan
              </td>
            </tr>
          </tbody>
        </v-table>

        <!-- Desktop Pagination -->
        <v-row v-if="itemsData && itemsData.length > 0">
          <v-col class="d-flex justify-center">
            <v-pagination
              :model-value="currentPage"
              :length="totalPages"
              :total-visible="7"
              @update:model-value="changePage"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Mobile List View -->
    <v-row class="md:hidden!">
      <v-col>
        <v-list lines="three">
          <template v-if="itemsData && itemsData.length > 0">
            <v-list-item
              v-for="(item, index) in itemsData"
              :key="item.id"
              class="py-3 my-1"
            >
              <div class="d-flex align-start justify-space-between w-100">
                <div class="grow pr-2">
                  <div class="d-flex align-center mb-1">
                    <span class="text-caption text-grey mr-2">{{ getItemNumber(index) }}.</span>
                    <span class="font-medium"
                      >{{ item.name }} <span class="font-normal">- {{ item.spesification_name }}</span></span
                    >
                  </div>
                  <div class="d-flex align-center text-body-2 gap-3">
                    <div v-if="canActItem($page.props.auth.user)">
                      <span class="text-grey">Harga:</span>
                      <span class="font-medium ml-1">{{ formatRupiah(item.price) }}</span>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-column align-end shrink-0">
                  <div class="text-body-2 font-medium mb-2">{{ item.stock }} {{ item.unit.name }}</div>
                  <div>
                    <v-btn
                      v-if="canActItem($page.props.auth.user)"
                      size="small"
                      icon="mdi-dots-vertical"
                      variant="text"
                    >
                  </v-btn>
                  <v-menu
                    v-if="canActItem($page.props.auth.user)"
                    activator="parent"
                  >
                    <v-list density="compact">
                      <v-list-item
                        value="edit"
                        @click="openEditDialog(item)"
                      >
                        <v-icon
                          icon="mdi-pencil"
                          class="mr-2"
                        />
                        Edit
                      </v-list-item>
                      <v-list-item value="delete">
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
                            <v-card-title class="bg-blue-darken-2 text-center text-wrap">Konfirmasi!</v-card-title>
                            <v-card-text>
                              <div>
                                Apakah Anda yakin ingin menghapus barang <span class="font-weight-bold text-blue-600">{{ item.name }}</span
                                >?
                              </div>
                            </v-card-text>
                            <v-card-actions>
                              <v-spacer></v-spacer>
                              <v-btn @click="isActive.value = false">Batal</v-btn>
                              <v-btn
                                color="error"
                                @click="
                                  deleteItem(item.id);
                                  isActive.value = false;
                                "
                              >
                                Hapus
                              </v-btn>
                            </v-card-actions>
                          </v-card>
                        </v-dialog>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                  </div>
                </div>
              </div>
              <div class="mt-3 border-b"></div>
            </v-list-item>
          </template>

          <v-list-item v-else>
            <v-list-item-title class="text-grey text-center"> Belum ada barang yang ditambahkan </v-list-item-title>
          </v-list-item>
        </v-list>

        <!-- Mobile Pagination -->
        <v-row v-if="itemsData && itemsData.length > 0">
          <v-col>
            <v-pagination
              :model-value="currentPage"
              :length="totalPages"
              :total-visible="5"
              @update:model-value="changePage"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Add/Edit Barang Dialog Form -->
    <v-dialog
      v-model="dialog"
      max-width="700px"
      persistent
    >
      <v-card>
        <v-card-title class="bg-blue-darken-2">
          <span class="text-h5">{{ editingId ? 'Edit Barang' : 'Tambah Barang Baru' }}</span>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-form @submit.prevent="submitForm">
              <v-row>
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                    v-model="form.name"
                    label="Nama Barang *"
                    :error-messages="form.errors.name"
                    placeholder="Contoh: Laptop Dell"
                    required
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-select
                    v-model="form.unit_id"
                    label="Satuan *"
                    :items="units"
                    item-title="name"
                    item-value="id"
                    :error-messages="form.errors.unit_id"
                    placeholder="Pilih satuan"
                    required
                    variant="outlined"
                  ></v-select>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12">
                  <v-textarea
                    v-model="form.spesification_name"
                    label="Spesifikasi *"
                    :error-messages="form.errors.spesification_name"
                    placeholder="Contoh: Core i5, RAM 8GB, SSD 256GB"
                    required
                    variant="outlined"
                    rows="3"
                  ></v-textarea>
                </v-col>
              </v-row>
              <v-row>
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                    v-model.number="form.stock"
                    label="Stok"
                    :error-messages="form.errors.stock"
                    type="number"
                    min="0"
                    placeholder="0"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                    v-model.number="form.price"
                    label="Harga"
                    :error-messages="form.errors.price"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="0"
                    prefix="Rp"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-form>
          </v-container>
          <small class="text-grey">* Wajib diisi</small>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey-darken-1"
            variant="text"
            :disabled="form.processing"
            @click="closeDialog"
          >
            Batal
          </v-btn>
          <v-btn
            color="primary"
            variant="tonal"
            :loading="form.processing"
            @click="submitForm"
          >
            {{ editingId ? 'Perbarui' : 'Simpan' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

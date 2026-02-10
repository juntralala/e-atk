<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  items: {
    type: [Array, null],
    default: [],
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
        const hasNonFieldError = Object.keys(errors).some(key => !fieldErrors.includes(key));
        
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
        const hasNonFieldError = Object.keys(errors).some(key => !fieldErrors.includes(key));
        
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
</script>

<template>
  <Head>
    <title>Data barang</title>
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
          first-part-title="Data"
          second-part-title="Barang"
        />
      </v-col>
    </v-row>

    <v-row>
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
              <th class="text-left">Harga</th>
              <th class="w-1/12 text-left">Tindakan</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in items"
              :key="item.id"
            >
              <td>{{ index + 1 }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.unit.name }}</td>
              <td>{{ item.spesification_name }}</td>
              <td>{{ item.stock }}</td>
              <td>{{ formatRupiah(item.price) }}</td>
              <td>
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
                              Apakah Anda yakin ingin menghapus barang <span class="text-blue-600 font-weight-bold">{{ item.name }}</span>?
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
            <tr v-if="!items || items.length === 0">
              <td
                colspan="7"
                class="text-grey text-center"
              >
                Belum ada barang yang ditambahkan
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-col>
    </v-row>

    <!-- Mobile Card View -->
    <v-row class="md:hidden!">
      <v-col>
        <v-row
          v-for="item in items"
          :key="item.id"
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
                                Apakah Anda yakin ingin menghapus barang <span class="text-blue-600 font-weight-bold">{{ item.name }}</span>?
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
                </v-btn>
              </v-card-actions>
              <v-card-text>
                <v-row>
                  <v-col cols="5">Nama Barang</v-col>
                  <v-col>{{ item.name }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Satuan</v-col>
                  <v-col>{{ item.unit.name }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Spesifikasi</v-col>
                  <v-col>{{ item.spesification_name }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Stok</v-col>
                  <v-col>{{ item.stock }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Harga</v-col>
                  <v-col>{{ formatRupiah(item.price) }}</v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <v-row v-if="!items || items.length === 0">
          <v-col>
            <v-card>
              <v-card-text class="text-center text-grey">
                Belum ada barang yang ditambahkan
              </v-card-text>
            </v-card>
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
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.name"
                    label="Nama Barang *"
                    :error-messages="form.errors.name"
                    placeholder="Contoh: Laptop Dell"
                    required
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
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
                <v-col cols="12" md="6">
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
                <v-col cols="12" md="6">
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
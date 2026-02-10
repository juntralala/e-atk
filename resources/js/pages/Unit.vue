<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  units: {
    type: [Array, null],
    default: [],
  },
});

const dialog = ref(false);
const editingId = ref(null);
const form = useForm({
  name: '',
});
const errorDialog = ref(false);
const errorMessage = ref('');

const openAddDialog = () => {
  editingId.value = null;
  form.reset();
  form.clearErrors();
  dialog.value = true;
};

const openEditDialog = (unit) => {
  editingId.value = unit.id;
  form.name = unit.name;
  form.clearErrors();
  dialog.value = true;
};

const submitForm = () => {
  if (editingId.value) {
    form.put(`/units/${editingId.value}`, {
      onSuccess: () => {
        dialog.value = false;
        form.reset();
        router.reload();
      },
      onError: (errors) => {
        // Jika ada error field 'name', biarkan ditampilkan di field
        // Jika ada error lain (non-field), tampilkan di dialog
        if (!errors.name) {
          errorMessage.value = errors.message || 'Terjadi kesalahan saat memperbarui satuan.';
          errorDialog.value = true;
          
          setTimeout(() => {
            errorDialog.value = false;
            errorMessage.value = '';
          }, 3000);
        }
      },
    });
  } else {
    form.post('/units', {
      onSuccess: () => {
        dialog.value = false;
        form.reset();
        router.reload();
      },
      onError: (errors) => {
        // Jika ada error field 'unit', biarkan ditampilkan di field
        // Jika ada error lain (non-field), tampilkan di dialog
        if (!errors.unit) {
          errorMessage.value = errors.message || 'Terjadi kesalahan saat menyimpan satuan.';
          errorDialog.value = true;
          
          setTimeout(() => {
            errorDialog.value = false;
            errorMessage.value = '';
          }, 3000);
        }
      },
    });
  }
};

const deleteUnit = (id) => {
  form.delete(`/units/${id}`, {
    onSuccess: () => {
      router.reload();
    },
    onError: (errors) => {
      errorMessage.value = errors.message || 'Terjadi kesalahan saat menghapus satuan.';
      errorDialog.value = true;
      
      setTimeout(() => {
        errorDialog.value = false;
        errorMessage.value = '';
      }, 3000);
    },
  });
};

const closeDialog = () => {
  dialog.value = false;
  form.reset();
  form.clearErrors();
};

const closeErrorDialog = () => {
  errorDialog.value = false;
  errorMessage.value = '';
};
</script>

<template>
  <v-container>
    <!-- Error Dialog - HANYA untuk error NON-FIELD -->
    <v-dialog
      v-model="errorDialog"
      max-width="400"
    >
      <v-card>
        <v-card-text class="pa-8 text-center">
          <v-icon
            icon="mdi-alert-circle"
            size="64"
            color="error"
            class="mb-4"
          ></v-icon>
          <h2 class="text-h5 font-weight-bold mb-2">Gagal!</h2>
          <p class="text-body-1">{{ errorMessage }}</p>
        </v-card-text>
        <v-card-actions class="justify-center pb-6">
          <v-btn
            color="error"
            variant="flat"
            @click="closeErrorDialog"
          >
            Tutup
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Data"
          second-part-title="Satuan Ukuran"
        />
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <v-btn
          variant="tonal"
          color="primary"
          prepend-icon="mdi-plus"
          @click="openAddDialog"
        >
          Tambah Satuan Ukuran
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-table class="borderless-table">
          <thead class="bg-blue-darken-2">
            <tr>
              <th class="w-1/12 text-left">No</th>
              <th class="text-left">Nama Satuan</th>
              <th class="w-1/12 text-left">Tindakan</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(unit, index) in units"
              :key="unit.id"
            >
              <td>{{ index + 1 }}</td>
              <td>{{ unit.name }}</td>
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
                      @click="openEditDialog(unit)"
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
                          <v-card-title class="text-center">Konfirmasi!</v-card-title>
                          <v-card-text>
                            Apakah Anda yakin ingin menghapus <span class="font-weight-bold">{{ unit.name }}</span>?
                          </v-card-text>
                          <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn @click="isActive.value = false">Batal</v-btn>
                            <v-btn
                              color="error"
                              @click="
                                deleteUnit(unit.id);
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
            <tr v-if="!units || units.length === 0">
              <td
                colspan="3"
                class="text-grey text-center"
              >
                Belum ada satuan yang ditambahkan
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-col>
    </v-row>

    <!-- Add/Edit Satuan Dialog Form -->
    <v-dialog
      v-model="dialog"
      max-width="600px"
      persistent
    >
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ editingId ? 'Edit Satuan Ukuran' : 'Tambah Satuan Ukuran Baru' }}</span>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-form @submit.prevent="submitForm">
              <v-row>
                <v-col cols="12">
                  <!-- Error FIELD ditampilkan di sini via :error-messages -->
                  <v-text-field
                    v-model="form.name"
                    label="Nama Satuan"
                    :error-messages="form.errors.name"
                    placeholder="e.g., pcs, kg, liter, box"
                    required
                    variant="outlined"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-form>
          </v-container>
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
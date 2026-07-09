<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  reasons: {
    type: Array,
    default: () => [],
  },
});

const successDialog = ref(false);
const errorDialog = ref(false);
const errorMessage = ref('');
const vuetifyForm = ref(null);

const form = useForm({
  notes: '',
  items: [
    {
      item_id: null,
      adjustment: 0,
      reason_id: null,
    },
  ],
});

function getItemFromProp(itemId) {
  return props.items.find((i) => i.id === itemId) || null;
}

function getNewStock(formItem) {
  const item = getItemFromProp(formItem.item_id);
  if (!item || formItem.adjustment === null || formItem.adjustment === '') return null;
  return item.stock + Number(formItem.adjustment);
}

// Rules validasi per item — cek agar stok baru tidak minus
function getAdjustmentRules(index) {
  return [
    (value) => {
      const formItem = form.items[index];
      const item = getItemFromProp(formItem.item_id);
      if (!item) return true; // belum pilih barang, skip
      const newStock = item.stock + Number(value);
      if (newStock < 0) {
        return `Stok tidak boleh minus. Maksimal pengurangan: -${item.stock}`;
      }
      return true;
    },
  ];
}

function addItem() {
  form.items.push({
    item_id: null,
    adjustment: 0,
    reason_id: null,
  });
}

function deleteItem(index) {
  if (form.items.length > 1) {
    form.items.splice(index, 1);
  }
}

function onItemSelected(index) {
  form.items[index].adjustment = 0;
}

async function submitForm() {
  const { valid } = await vuetifyForm.value.validate();
  if (!valid) return;

  const payload = {
    notes: form.notes,
    items: form.items.map((i) => {
      return {
        item_id: i.item_id,
        adjustment: i.adjustment,
        reason_id: i.reason_id,
      };
    }),
  };

  form
    .transform(() => payload)
    .post(route('stock.adjustments.create'), {
      onSuccess: () => {
        successDialog.value = true;
        resetForm();
      },
      onError: (errors) => {
        console.error('Validation errors:', errors);
        errorDialog.value = true;
        if (errors.error || errors.message) {
          errorMessage.value = errors.error || errors.message;
        } else {
          const firstError = Object.values(errors)[0];
          errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
        }
      },
    });
}

function resetForm() {
  vuetifyForm.value?.reset(); // reset state validasi Vuetify
  form.reset();
  form.notes = '';
  form.items = [
    {
      item_id: null,
      adjustment: 0,
      reason_id: null,
    },
  ];
}

function cancel() {
  resetForm();
}

function isItemDisabled(itemId, currentIndex) {
  return form.items.some((i, idx) => idx !== currentIndex && i.item_id === itemId);
}
</script>

<template>
  <v-container fluid class="pa-4 pa-md-6">
    <SuccessDialog v-model="successDialog" message="Penyesuaian stok berhasil disimpan." />
    <AlertDialog title="Gagal!" v-model="errorDialog" :message="errorMessage" />

    <!-- Header -->
    <v-row class="mb-4">
      <v-col>
        <PageTitleHighlightPart first-part-title="Penyesuaian" second-part-title="Stok" />
      </v-col>
    </v-row>

    <!-- Main Form — dibungkus v-form untuk validasi Vuetify -->
    <v-row>
      <v-col>
        <v-form ref="vuetifyForm" @submit.prevent="submitForm">
          <!-- Notes -->
          <v-row class="mb-4">
            <v-col cols="12">
              <v-textarea
                v-model="form.notes"
                label="Catatan (opsional)"
                placeholder="Tuliskan alasan penyesuaian stok secara umum..."
                :error-messages="form.errors.notes"
                density="comfortable"
                variant="outlined"
                color="blue"
                rows="2"
                auto-grow />
            </v-col>
          </v-row>

          <v-divider class="mb-4" />

          <!-- Item Rows -->
          <div
            v-for="(item, index) in form.items"
            :key="index"
            class="pa-4 mb-4 rounded-lg"
            :class="index % 2 === 0 ? 'bg-grey-lighten-4' : 'bg-white'">
            <!-- Row header -->
            <div class="d-flex align-center mb-3 gap-2">
              <v-chip size="small" color="blue" variant="flat">
                {{ index + 1 }}
              </v-chip>
              <span class="text-body-2 text-grey-darken-2 font-weight-medium">Barang {{ index + 1 }}</span>
              <v-spacer />
              <v-btn
                variant="text"
                color="red-darken-1"
                icon
                size="small"
                :disabled="form.items.length === 1"
                @click="deleteItem(index)">
                <v-icon size="20">mdi-close</v-icon>
              </v-btn>
            </div>

            <v-row>
              <!-- Pilih Barang -->
              <v-col cols="12" md="4">
                <v-autocomplete
                  v-model="item.item_id"
                  density="comfortable"
                  :items="items"
                  item-title="name"
                  item-value="id"
                  label="Pilih Barang"
                  placeholder="Ketik untuk mencari..."
                  :error-messages="form.errors[`items.${index}.item_id`]"
                  variant="outlined"
                  color="blue"
                  bg-color="white"
                  @update:model-value="onItemSelected(index)">
                  <template v-slot:item="{ props: itemProps, item: barangItem }">
                    <v-list-item
                      v-bind="itemProps"
                      :disabled="isItemDisabled(barangItem.raw.id, index)"
                      :subtitle="`Stok: ${barangItem.raw.stock} ${barangItem.raw.unit?.name ?? ''}`" />
                  </template>
                </v-autocomplete>
              </v-col>

              <!-- Stok Saat Ini (read only) -->
              <v-col cols="12" sm="6" md="2">
                <v-text-field
                  :model-value="getItemFromProp(item.item_id)?.stock ?? '-'"
                  label="Stok Saat Ini"
                  density="comfortable"
                  variant="outlined"
                  bg-color="white"
                  readonly
                  disabled />
              </v-col>

              <!-- Perubahan (delta ±) dengan validasi -->
              <v-col cols="12" sm="6" md="2">
                <v-number-input
                  v-model="item.adjustment"
                  control-variant="split"
                  density="comfortable"
                  label="Perubahan"
                  :rules="getAdjustmentRules(index)"
                  :hint="
                    item.adjustment > 0
                      ? 'Penambahan stok'
                      : item.adjustment < 0
                        ? 'Pengurangan stok'
                        : 'Tidak ada perubahan'
                  "
                  persistent-hint
                  :error-messages="form.errors[`items.${index}.new_stock`]"
                  variant="outlined"
                  :color="item.adjustment > 0 ? 'green' : item.adjustment < 0 ? 'red' : 'blue'"
                  bg-color="white"
                  :disabled="!item.item_id" />
              </v-col>

              <!-- Stok Baru (kalkulasi otomatis, read only) -->
              <v-col cols="12" sm="6" md="2">
                <v-text-field
                  :model-value="getNewStock(item) !== null ? getNewStock(item) : '-'"
                  label="Stok Baru"
                  density="comfortable"
                  variant="outlined"
                  :bg-color="getNewStock(item) !== null && getNewStock(item) === 0 ? 'orange-lighten-5' : 'white'"
                  readonly
                  disabled />
              </v-col>

              <!-- Alasan -->
              <v-col cols="12" sm="6" md="2">
                <v-autocomplete
                  v-model="item.reason_id"
                  density="comfortable"
                  :items="reasons"
                  item-title="reason"
                  item-value="id"
                  label="Alasan"
                  placeholder="Pilih alasan..."
                  :error-messages="form.errors[`items.${index}.reason_id`]"
                  variant="outlined"
                  color="blue"
                  bg-color="white"
                  :disabled="!item.item_id" />
              </v-col>
            </v-row>
          </div>

          <!-- Tambah Barang -->
          <v-row class="mt-2">
            <v-col>
              <v-btn variant="outlined" color="blue" :disabled="form.processing" @click="addItem" block>
                <v-icon icon="mdi-plus" start />
                Tambah Barang
              </v-btn>
            </v-col>
          </v-row>

          <v-divider class="my-6" />

          <!-- Action Buttons -->
          <v-row class="mt-4">
            <v-col class="d-flex justify-end gap-3">
              <v-btn variant="outlined" color="grey-darken-1" :disabled="form.processing" @click="cancel">
                <v-icon icon="mdi-close" start />
                Batal
              </v-btn>
              <v-btn type="submit" variant="flat" color="blue" :loading="form.processing" :disabled="form.processing">
                <v-icon icon="mdi-content-save" start />
                Buat
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

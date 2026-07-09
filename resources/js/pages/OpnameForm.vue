<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import DatePicker from '@/components/molecules/DatePicker.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
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

const form = useForm({
  opname_date: new Date().toISOString().split('T')[0],
  opnames: [
    {
      item_id: null,
      system_stock: 0,
      physical_stock: null,
      reason_id: null,
      remark: null,
    },
  ],
});

function addOpnameInput() {
  form.opnames.push({
    item_id: null,
    system_stock: 0,
    physical_stock: null,
    reason_id: null,
    remark: null,
  });
}

function deleteOpname(index) {
  if (form.opnames.length > 1) {
    form.opnames.splice(index, 1);
  }
}

function getItemFromProp(itemId) {
  return props.items.find((i) => i.id === itemId);
}

function getDiff(opname) {
  if (opname.physical_stock === null || opname.physical_stock === '') return null;
  return Number(opname.physical_stock) - Number(opname.system_stock);
}

function getDiffColor(diff) {
  if (diff === null) return 'default';
  if (diff > 0) return 'success';
  if (diff < 0) return 'error';
  return 'default';
}

function cancel() {
  form.reset();
  form.opname_date = new Date().toISOString().split('T')[0];
  form.opnames = [{ item_id: null, system_stock: 0, physical_stock: null, reason_id: null, remark: null }];
}

function submitForm() {
  form.post(route('stock-opname.store'), {
    onSuccess: () => {
      successDialog.value = true;
      cancel();
    },
    onError: (errors) => {
      errorDialog.value = true;
      const firstError = errors.error || errors.message || Object.values(errors)[0];
      errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
    },
  });
}
</script>

<template>
  <Head>
    <title>Formulir Stock Opname</title>
  </Head>

  <v-container fluid class="pa-4 pa-md-6">
    <SuccessDialog v-model="successDialog" message="Stock opname berhasil disimpan." />
    <AlertDialog title="Gagal!" v-model="errorDialog" :message="errorMessage" />

    <v-row class="mb-4">
      <v-col>
        <PageTitleHighlightPart first-part-title="Stock" second-part-title="Opname" />
      </v-col>
    </v-row>

    <form @submit.prevent="submitForm">
      <v-row class="mb-3">
        <v-col cols="12" md="3">
          <DatePicker
            v-model="form.opname_date"
            label="Tanggal Opname"
            :error-messages="form.errors.opname_date"
            density="compact"
            variant="outlined"
            color="blue"
            hide-details />
        </v-col>
      </v-row>

      <v-divider class="mb-3" />

      <!-- Column Headers -->
      <v-row class="mb-1 px-1" dense>
        <v-col cols="1" class="text-caption text-grey-darken-1 font-weight-medium">No</v-col>
        <v-col cols="3" class="text-caption text-grey-darken-1 font-weight-medium">Barang</v-col>
        <v-col cols="1" class="text-caption text-grey-darken-1 font-weight-medium">Stok Sistem</v-col>
        <v-col cols="1" class="text-caption text-grey-darken-1 font-weight-medium">Stok Fisik</v-col>
        <v-col cols="1" class="text-caption text-grey-darken-1 font-weight-medium">Selisih</v-col>
        <v-col cols="2" class="text-caption text-grey-darken-1 font-weight-medium">Sebab</v-col>
        <v-col cols="2" class="text-caption text-grey-darken-1 font-weight-medium">Deskripsi</v-col>
        <v-col cols="1" />
      </v-row>

      <v-row
        v-for="(opname, index) in form.opnames"
        :key="index"
        dense
        align="center"
        class="mb-1 rounded px-1 py-1"
        :class="index % 2 === 0 ? 'bg-grey-lighten-4' : ''">
        <v-col cols="1">
          <span class="text-caption text-grey-darken-1">{{ index + 1 }}</span>
        </v-col>

        <v-col cols="3">
          <v-autocomplete
            v-model="opname.item_id"
            :items="items"
            item-title="name"
            item-value="id"
            placeholder="Pilih barang..."
            density="compact"
            variant="outlined"
            color="blue"
            bg-color="white"
            hide-details
            :error="!!form.errors[`opnames.${index}.item_id`]"
            @update:model-value="
              (val) => {
                const found = getItemFromProp(val);
                if (found) opname.system_stock = found.stock;
              }
            ">
            <template #no-data>
              <div class="pa-2 text-caption text-center">Tidak ada barang tersedia</div>
            </template>
          </v-autocomplete>
        </v-col>

        <v-col cols="1">
          <v-number-input
            v-model="opname.system_stock"
            density="compact"
            variant="outlined"
            control-variant="hidden"
            bg-color="white"
            hide-details
            readonly />
        </v-col>

        <v-col cols="1">
          <v-number-input
            v-model="opname.physical_stock"
            :min="0"
            density="compact"
            variant="outlined"
            control-variant="stacked"
            color="blue"
            bg-color="white"
            hide-details
            :error="!!form.errors[`opnames.${index}.physical_stock`]" />
        </v-col>

        <v-col cols="1" class="">
          <v-chip
            v-if="getDiff(opname) !== null"
            :color="getDiffColor(getDiff(opname))"
            size="large"
            variant="text"
            label>
            {{ getDiff(opname) > 0 ? '+' : '' }}{{ getDiff(opname) }}
          </v-chip>
          <span v-else class="text-caption text-grey">—</span>
        </v-col>

        <v-col cols="2">
          <v-select
            v-model="opname.reason_id"
            :items="reasons"
            item-title="reason"
            item-value="id"
            placeholder="Pilih..."
            density="compact"
            variant="outlined"
            color="blue"
            bg-color="white"
            hide-details
            :error="!!form.errors[`opnames.${index}.reason_id`]">
            <template #no-data>
              <div class="pa-2 text-caption text-center">Tidak ada pilihan sebab tersedia</div>
            </template>
          </v-select>
        </v-col>

        <v-col cols="2">
          <v-text-field
            v-model="opname.remark"
            placeholder="Opsional..."
            density="compact"
            variant="outlined"
            color="blue"
            bg-color="white"
            hide-details />
        </v-col>

        <!-- Delete -->
        <v-col cols="1" class="d-flex justify-center">
          <v-btn
            variant="text"
            color="red-darken-1"
            icon
            size="x-small"
            :disabled="form.opnames.length === 1"
            @click="deleteOpname(index)">
            <v-icon size="16">mdi-close</v-icon>
          </v-btn>
        </v-col>
      </v-row>

      <v-divider class="my-4" />

      <!-- Add + Actions -->
      <v-row align="center">
        <v-col cols="12" sm="6">
          <v-btn variant="outlined" color="blue" :disabled="form.processing" @click="addOpnameInput">
            <v-icon icon="mdi-plus" start />
            Tambah Barang
          </v-btn>
        </v-col>
        <v-col cols="12" sm="6" class="d-flex justify-end gap-2">
          <v-btn variant="outlined" color="grey-darken-1" :disabled="form.processing" @click="cancel">
            <v-icon icon="mdi-close" start />
            Batal
          </v-btn>
          <v-btn type="submit" variant="flat" color="blue" :loading="form.processing" :disabled="form.processing">
            <v-icon icon="mdi-content-save" start />
            Simpan
          </v-btn>
        </v-col>
      </v-row>
    </form>
  </v-container>
</template>

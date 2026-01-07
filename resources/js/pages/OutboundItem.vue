<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import axios from 'axios';
import { useDisplay } from 'vuetify/lib/composables/display';
import DateTimePickerInput from '@/components/molecules/DateTimePickerInput.vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  items: Array,
  measurementUnits: Array,
  recipients: Array,
});

const skuSelectionItems = props.items.reduce((accumulator, item) => {
  accumulator.push({ type: "subheader", title: item.name });
  item.skus.forEach(sku => {
    accumulator.push({ value: sku.id, title: sku.sku, raw: item });
  });
  return accumulator;
}, []);

const { xs } = useDisplay();
const successDialog = ref(false);
const errorDialog = ref(false);
const errorMessage = ref('');

// Watch perubahan recipient_id untuk auto-fill division
function onRecipientChange(recipient) {
  if (!recipient) {
    console.warn('recive recipient null');
    return;
  }
  if (recipient instanceof String || typeof recipient === 'string') {
    console.log('recive recipient string: ' + recipient);
    return;
  }
  if (recipient.division) {
    form.division = recipient.division;
  }
}

const form = useForm({
  recipient_id: null,
  division: '',
  transaction_date: new Date(),
  notes: '',
  transaction_items: [
    {
      item_id: null,
      unit_id: null,
      quantity: 1,
      supportedUnits: []
    },
  ],
});

function addItem() {
  form.transaction_items.push({
    item_id: null,
    unit_id: null,
    quantity: 1,
  });
}

function deleteItem(index) {
  if (form.transaction_items.length > 1) {
    form.transaction_items.splice(index, 1);
  }
}

function submitTransaction() {
  form
    .transform((data) => {
      if (data.transaction_date instanceof Date) {
        data.transaction_date = data.transaction_date?.toISOString();
      }
      if (!(data.recipient_id instanceof String)) {
        data.recipient_id = data?.recipient_id?.id;
      }
      data.transaction_items = data.transaction_items.map(item => {
        return {
          sku_id: item.sku_id,
          unit_id: item.unit_id,
          quantity: item.quantity,
        };
      });
      return data;
    })
    .post(route('items.outbound'), {
      onSuccess: () => {
        successDialog.value = true;
        form.reset();
      },
      onError: (errors) => {
        console.error('Validation errors:', errors);
        errorDialog.value = true;
        // Ambil pesan error pertama atau error umum
        if (errors.error) {
          errorMessage.value = errors.error;
        } else {
          const firstError = Object.values(errors)[0];
          errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
        }
      },
    });
}

function cancel() {
  form.reset();
}

const recipientItems = computed(() => {
  return props.recipients.map((item) => {
    item.name_nickname = item.nickname ? `${item.name} (${item.nickname})` : item.name;
    return { ...item };
  });
});

async function fetchSupportedMeasurementUnitBySkuId(skuId) {
  return (await axios.get(route('items.skus.units.by-sku-id', skuId))).data?.data;
}

watch(() => form.transaction_items, async (items) => {
  for (let i = 0; i < items.length; i++) {
    const item = items[i];

    // Jika sku_id berubah, fetch supported units
    if (item.sku_id && (!item.supportedUnits || item.supportedUnits.length === 0)) {
      try {
        const supportedUnits = await fetchSupportedMeasurementUnitBySkuId(item.sku_id);
        item.supportedUnits = supportedUnits;

        // Reset unit_id jika unit yang dipilih tidak ada di supported units
        if (item.unit_id && !supportedUnits.find(u => u.id === item.unit_id)) {
          item.unit_id = null;
        }
      } catch (error) {
        console.error('Error fetching supported units:', error);
      }
    }
  }
}, { deep: true });
</script>

<template>
  <v-container fluid>
    <SuccessDialog v-model="successDialog" title="Berhasil!" message="Transaksi barang keluar berhasil disimpan." />
    <AlertDialog v-model="errorDialog" title="Gagal!" :message="errorMessage" />

    <v-row>
      <v-col>
        <PageTitleHighlightPart first-part-title="Barang" second-part-title="Keluar" />
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <form @submit.prevent="submitTransaction">
          <!-- Header Transaction Fields -->
          <v-row class="pt-4">
            <v-col cols="12" md="4">
              <v-combobox v-model="form.recipient_id" density="comfortable" :items="recipientItems"
                item-title="name_nickname" label="Penerima" :error-messages="form.errors.recipient_id"
                @update:model-value="onRecipientChange" />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field v-model="form.division" density="comfortable" label="Divisi (opsional)"
                :error-messages="form.errors.division" persistent-hint />
            </v-col>
            <v-col cols="12" md="4">
              <DateTimePickerInput label="Tanggal Transaksi" v-model="form.transaction_date" density="comfortable"
                :error-messages="form.errors.transaction_date" />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12">
              <v-textarea v-model="form.notes" density="comfortable" label="Catatan (opsional)" rows="2"
                :error-messages="form.errors.notes" />
            </v-col>
          </v-row>

          <v-divider class="my-6"></v-divider>

          <!-- Transaction Items -->
          <v-row v-for="(item, index) in form.transaction_items" :key="index" class="items-start! odd:bg-gray-100 pt-5">
            <v-col class="py-0!" cols="11" md="">
              <v-autocomplete v-model="item.sku_id" density="comfortable" :items="skuSelectionItems" label="SKU"
                @update:model-value="() => {
                  // Reset supportedUnits agar watcher bisa fetch ulang
                  item.supportedUnits = [];
                  item.unit_id = null;
                }" :error-messages="form.errors[`transaction_items.${index}.sku_id`]">
              </v-autocomplete>
            </v-col>
            <v-col v-if="xs" cols="1" class="ps-0 mt-1">
              <v-btn variant="text" size="25" rounded="full" color="red" icon
                :disabled="form.transaction_items.length === 1" @click="deleteItem(index)">
                <v-icon icon="mdi-minus" size="18"></v-icon>
              </v-btn>
            </v-col>
            <v-col class="py-0!" cols="6" md="2">
              <v-autocomplete v-model="item.unit_id" density="comfortable" :items="item.supportedUnits"
                item-title="name" item-value="id" label="Satuan"
                :disabled="!item.sku_id || item.supportedUnits.length === 0"
                :error-messages="form.errors[`transaction_items.${index}.unit_id`]" />
            </v-col>
            <v-col class="py-0!" cols="6" md="2">
              <v-number-input v-model="item.quantity" :min="1" control-variant="split" density="comfortable"
                label="Jumlah" :error-messages="form.errors[`transaction_items.${index}.quantity`]" />
            </v-col>
            <v-col v-if="!xs" cols="auto" class="mt-1 py-0!">
              <v-btn variant="tonal" color="red" icon size="small" :disabled="form.transaction_items.length === 1"
                @click="deleteItem(index)">
                <v-icon icon="mdi-minus"></v-icon>
              </v-btn>
            </v-col>
          </v-row>

          <v-row class="mt-7 md:mt-2">
            <v-col class="flex items-center! justify-end! pt-0">
              <v-btn @click="addItem" variant="tonal" color="blue-accent-2" :disabled="form.processing">
                <v-icon icon="mdi-plus" start></v-icon>
                Tambah Barang
              </v-btn>
            </v-col>
          </v-row>

          <v-divider class="my-4 md:my-6"></v-divider>

          <v-row>
            <v-col class="flex justify-end gap-4">
              <v-btn @click="cancel" variant="tonal" color="grey" :disabled="form.processing"> Batal </v-btn>
              <v-btn type="submit" variant="tonal" color="blue-accent-3" :loading="form.processing"
                :disabled="form.processing">
                <v-icon icon="mdi-content-save" start></v-icon>
                Submit
              </v-btn>
            </v-col>
          </v-row>
        </form>
      </v-col>
    </v-row>
  </v-container>
</template>

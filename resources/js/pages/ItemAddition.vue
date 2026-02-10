<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useDisplay } from 'vuetify/lib/composables/display';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
});

const { xs } = useDisplay();
const successDialog = ref(false);
const errorDialog = ref(false);
const errorMessage = ref('');

const form = useForm({
  addition_date: new Date().toISOString().split('T')[0],
  items: [
    {
      item_id: null,
      quantity: 1,
      price: 0,
    },
  ],
});

function addItem() {
  form.items.push({
    item_id: null,
    quantity: 1,
    price: 0,
  });
}

function deleteItem(index) {
  if (form.items.length > 1) {
    form.items.splice(index, 1);
  }
}

function getItemFromProp(barangId) {
  return props.items.find((b) => b.id === barangId);
}

function submitTransaction() {
  form.post(route('items.additions.create'), {
    onSuccess: () => {
      successDialog.value = true;
      form.reset();
      form.addition_date = new Date().toISOString().split('T')[0];
      form.items = [
        {
          item_id: null,
          quantity: 1,
          price: 0,
        },
      ];
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

function cancel() {
  form.reset();
  form.addition_date = new Date().toISOString().split('T')[0];
  form.items = [
    {
      item_id: null,
      quantity: 1,
      price: 0,
    },
  ];
}

// Format harga ke Rupiah
const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value);
};

// Watch untuk auto-fill harga ketika barang dipilih
watch(
  () => form.items,
  (items) => {
    items.forEach((item) => {
      if (item.item_id && item.price === 0) {
        const itemFromProp = getItemFromProp(item.item_id);
        if (itemFromProp) {
          item.price = parseFloat(itemFromProp.price);
        }
      }
    });
  },
  { deep: true },
);
</script>

<template>
  <v-container fluid>
    <SuccessDialog
      v-model="successDialog"
      message="Penambahan barang berhasil disimpan."
    />
    <AlertDialog
      title="Gagal!"
      v-model="errorDialog"
      :message="errorMessage"
    />
    
    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Penambahan"
          second-part-title="Barang"
        />
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <form @submit.prevent="submitTransaction">
          <!-- Header Transaction Fields -->
          <v-row class="pt-4">
            <v-col
              cols="12"
              md="6"
            >
              <v-text-field
                v-model="form.addition_date"
                type="date"
                density="comfortable"
                label="Tanggal Penambahan"
                :error-messages="form.errors.addition_date"
              />
            </v-col>
          </v-row>

          <v-divider class="my-6"></v-divider>

          <!-- Transaction Items -->
          <v-row
            v-for="(item, index) in form.items"
            :key="index"
            class="items-start! pt-5 odd:bg-gray-100!"
          >
            <v-col
              class="py-0"
              cols="11"
              md=""
            >
              <v-autocomplete
                v-model="item.item_id"
                density="comfortable"
                :items="items"
                item-title="name"
                item-value="id"
                label="Barang"
                :error-messages="form.errors[`items.${index}.item_id`]"
              >
                <template v-slot:item="{ props: itemProps, item: barangItem }">
                  <v-list-item
                    v-bind="itemProps"
                    :subtitle="`${barangItem.raw.spesification_name} - Stok: ${barangItem.raw.stock} ${barangItem.raw.unit.name}`"
                  />
                </template>
              </v-autocomplete>
            </v-col>

            <v-col
              v-if="xs"
              cols="1"
              class="mt-1 ps-0"
            >
              <v-btn
                variant="text"
                size="25"
                rounded="full"
                color="red"
                icon
                :disabled="form.items.length === 1"
                @click="deleteItem(index)"
              >
                <v-icon
                  icon="mdi-delete"
                  size="24"
                ></v-icon>
              </v-btn>
            </v-col>

            <v-col
              class="py-0!"
              cols="6"
              lg="2"
            >
              <v-number-input
                v-model="item.quantity"
                :min="1"
                control-variant="split"
                density="comfortable"
                label="Jumlah"
                :error-messages="form.errors[`items.${index}.quantity`]"
              />
            </v-col>

            <v-col
              class="py-0!"
              cols="6"
              lg="3"
            >
              <v-text-field
                v-model.number="item.price"
                type="number"
                :min="0"
                step="0.01"
                density="comfortable"
                label="Harga Satuan"
                prefix="Rp"
                :error-messages="form.errors[`items.${index}.price`]"
              />
            </v-col>

            <v-col
              cols="auto"
              class="mt-1 py-0!"
              v-if="!xs"
            >
              <v-btn
                variant="text"
                color="red"
                icon
                size="small"
                :disabled="form.items.length === 1"
                @click="deleteItem(index)"
              >
                <v-icon size="28" icon="mdi-delete"></v-icon>
              </v-btn>
            </v-col>
          </v-row>

          <v-row class="mt-7 md:mt-2">
            <v-col class="flex items-center! justify-end! pt-0">
              <v-btn
                variant="tonal"
                color="blue-accent-2"
                :disabled="form.processing"
                @click="addItem"
              >
                <v-icon
                  icon="mdi-plus"
                  start
                ></v-icon>
                Tambah Barang
              </v-btn>
            </v-col>
          </v-row>

          <v-divider class="my-4 md:my-6"></v-divider>

          <!-- Summary -->
          <v-row v-if="false && form.items.length > 0">
            <v-col
              cols="12"
              class="text-right"
            >
              <div class="text-h6 font-weight-bold">
                Total: {{ formatRupiah(form.items.reduce((sum, item) => sum + item.quantity * item.price, 0)) }}
              </div>
            </v-col>
          </v-row>

          <v-row>
            <v-col class="flex justify-end gap-4">
              <v-btn
                variant="tonal"
                color="grey"
                :disabled="form.processing"
                @click="cancel"
              >
                Batal
              </v-btn>
              <v-btn
                type="submit"
                variant="tonal"
                color="blue"
                :loading="form.processing"
                :disabled="form.processing"
              >
                <v-icon
                  icon="mdi-content-save"
                  start
                ></v-icon>
                Simpan
              </v-btn>
            </v-col>
          </v-row>
        </form>
      </v-col>
    </v-row>
  </v-container>
</template>

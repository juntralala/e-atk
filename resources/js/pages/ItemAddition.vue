<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import DatePicker from '@/components/molecules/DatePicker.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
});

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

// Computed total
const totalAmount = () => {
  return form.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
};
</script>

<template>
  <v-container
    fluid
    class="pa-4 pa-md-6"
  >
    <SuccessDialog
      v-model="successDialog"
      message="Penambahan barang berhasil disimpan."
    />
    <AlertDialog
      title="Gagal!"
      v-model="errorDialog"
      :message="errorMessage"
    />

    <!-- Header Section -->
    <v-row class="mb-4">
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Penambahan"
          second-part-title="Barang"
        />
      </v-col>
    </v-row>

    <!-- Main Form -->
    <v-row>
      <v-col>
        <form @submit.prevent="submitTransaction">
          <!-- Date Field -->
          <v-row class="mb-4">
            <v-col
              cols="12"
              md="6"
            >
              <DatePicker
                label="Tanggal Penambahan"
                v-model="form.addition_date"
                :error-messages="form.errors.addition_date"
                density="comfortable"
                variant="outlined"
                color="blue"
              />
            </v-col>
          </v-row>

          <v-divider class="mb-4"></v-divider>

          <!-- Transaction Items -->
          <div
            v-for="(item, index) in form.items"
            :key="index"
            class="pa-4 mb-4 rounded-lg"
            :class="index % 2 === 0 ? 'bg-grey-lighten-4' : 'bg-white'"
          >
            <div class="d-flex align-center mb-3 gap-2">
              <v-chip
                size="small"
                color="blue"
                variant="flat"
              >
                {{ index + 1 }}
              </v-chip>
              <span class="text-body-2 text-grey-darken-2 font-weight-medium"> Barang {{ index + 1 }} </span>
              <v-spacer></v-spacer>
              <v-btn
                variant="text"
                color="red-darken-1"
                icon
                size="small"
                :disabled="form.items.length === 1"
                @click="deleteItem(index)"
              >
                <v-icon size="20">mdi-close</v-icon>
              </v-btn>
            </div>

            <v-row>
              <!-- Item Selection -->
              <v-col
                cols="12"
                md="6"
              >
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
                >
                  <template v-slot:item="{ props: itemProps, item: barangItem }">
                    <v-list-item
                      v-bind="itemProps"
                      :subtitle="`${barangItem.raw.spesification_name} - Stok: ${barangItem.raw.stock} ${barangItem.raw.unit.name}`"
                    />
                  </template>
                </v-autocomplete>
              </v-col>

              <!-- Quantity -->
              <v-col
                cols="12"
                sm="6"
                md="3"
              >
                <v-number-input
                  v-model="item.quantity"
                  :min="1"
                  control-variant="split"
                  density="comfortable"
                  label="Jumlah"
                  :error-messages="form.errors[`items.${index}.quantity`]"
                  variant="outlined"
                  color="blue"
                  bg-color="white"
                />
              </v-col>

              <!-- Price -->
              <v-col
                cols="12"
                sm="6"
                md="3"
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
                  variant="outlined"
                  color="blue"
                  bg-color="white"
                />
              </v-col>
            </v-row>

            <!-- Subtotal per item -->
            <div
              v-if="item.item_id && item.quantity && item.price"
              class="mt-2 text-right"
            >
              <span class="text-body-2 text-grey-darken-1">
                Subtotal: <strong>{{ formatRupiah(item.quantity * item.price) }}</strong>
              </span>
            </div>
          </div>

          <!-- Add Item Button -->
          <v-row class="mt-2">
            <v-col>
              <v-btn
                variant="outlined"
                color="blue"
                :disabled="form.processing"
                @click="addItem"
                block
              >
                <v-icon
                  icon="mdi-plus"
                  start
                ></v-icon>
                Tambah Barang
              </v-btn>
            </v-col>
          </v-row>

          <v-divider class="my-6"></v-divider>

          <!-- Total Summary -->
          <v-row v-if="form.items.some((item) => item.item_id && item.quantity && item.price)">
            <v-col class="text-right">
              <div class="d-inline-block pa-4 bg-blue-lighten-5 rounded-lg">
                <span class="text-body-1 text-grey-darken-2 mr-3">Total Keseluruhan:</span>
                <span class="text-h6 font-weight-bold text-blue-darken-2">
                  {{ formatRupiah(totalAmount()) }}
                </span>
              </div>
            </v-col>
          </v-row>

          <!-- Action Buttons -->
          <v-row class="mt-4">
            <v-col class="d-flex justify-end gap-3">
              <v-btn
                variant="outlined"
                color="grey-darken-1"
                :disabled="form.processing"
                @click="cancel"
              >
                <v-icon
                  icon="mdi-close"
                  start
                ></v-icon>
                Batal
              </v-btn>
              <v-btn
                type="submit"
                variant="flat"
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

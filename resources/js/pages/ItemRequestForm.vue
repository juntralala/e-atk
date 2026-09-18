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
});

const successDialog = ref(false);
const errorDialog = ref(false);
const errorMessage = ref('');

const form = useForm({
  request_date: new Date().toISOString().split('T')[0],
  items: [
    {
      item_id: null,
      requested_quantity: 1,
    },
  ],
});

function addItem() {
  form.items.push({
    item_id: null,
    requested_quantity: 1,
  });
}

function deleteItem(index) {
  if (form.items.length > 1) {
    form.items.splice(index, 1);
  }
}

function getItemFromProp(itemId) {
  return props.items.find((item) => item.id === itemId);
}

function submitRequest() {
  form.post(route('items.requests.create'), {
    onSuccess: () => {
      successDialog.value = true;
      form.reset();
      form.request_date = new Date().toISOString().split('T')[0];
      form.items = [
        {
          item_id: null,
          requested_quantity: 1,
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
  form.request_date = new Date().toISOString().split('T')[0];
  form.items = [
    {
      item_id: null,
      requested_quantity: 1,
    },
  ];
}
</script>

<template>
  <v-container fluid class="pa-4 pa-md-6">
    <SuccessDialog v-model="successDialog" message="Permintaan barang berhasil disimpan." />
    <AlertDialog title="Gagal!" v-model="errorDialog" :message="errorMessage" />

    <!-- Header Section -->
    <v-row class="mb-4">
      <v-col>
        <PageTitleHighlightPart first-part-title="Minta" second-part-title="Barang" />
      </v-col>
    </v-row>

    <!-- Main Form -->
    <v-row>
      <v-col>
        <form @submit.prevent="submitRequest">
          <!-- Request Items -->
          <div
            v-for="(item, index) in form.items"
            :key="index"
            class="pa-4 mb-4 rounded-lg"
            :class="index % 2 === 0 ? 'bg-grey-lighten-4' : 'bg-white'">
            <div class="d-flex align-center mb-3 gap-2">
              <v-chip size="small" color="blue" variant="flat">
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
                @click="deleteItem(index)">
                <v-icon size="20">mdi-close</v-icon>
              </v-btn>
            </div>

            <v-row>
              <!-- Item Selection -->
              <v-col cols="12" md="8">
                <v-autocomplete

                  v-model="item.item_id"
                  density="comfortable"
                  :items="items"
                  item-title="name"
                  item-value="id"
                  label="Pilih Barang"
                  placeholder="Ketik untuk mencari..."
                  :hint="
                    item.item_id
                      ? `${getItemFromProp(item.item_id)?.spesification_name} - Stok: ${getItemFromProp(item.item_id)?.stock} ${getItemFromProp(item.item_id)?.unit?.name}`
                      : ''
                  "
                  persistent-hint
                  :error-messages="form.errors[`items.${index}.item_id`]"
                  variant="outlined"
                  color="blue"
                  bg-color="white">
                  <template v-slot:item="{ props: itemProps, item: barangItem }">
                    <v-list-item
                      v-bind="itemProps"
                      :subtitle="`${barangItem.raw.spesification_name} - Stok: ${barangItem.raw.stock} ${barangItem.raw.unit.name}`" />
                  </template>
                  <template #no-data>
                    <v-list-item>
                      <v-list-item-title>
                        Data item belum diinputkan admin
                      </v-list-item-title>
                    </v-list-item>
                  </template>
                </v-autocomplete>
              </v-col>

              <!-- Quantity -->
              <v-col cols="12" md="4">
                <v-number-input
                  v-model="item.requested_quantity"
                  :min="1"
                  control-variant="split"
                  density="comfortable"
                  label="Jumlah Diminta"
                  :error-messages="form.errors[`items.${index}.requested_quantity`]"
                  variant="outlined"
                  color="blue"
                  bg-color="white" />
              </v-col>
            </v-row>
          </div>

          <!-- Add Item Button -->
          <v-row class="mt-2">
            <v-col>
              <v-btn variant="outlined" color="blue" :disabled="form.processing" @click="addItem" block>
                <v-icon icon="mdi-plus" start></v-icon>
                Tambah Barang
              </v-btn>
            </v-col>
          </v-row>

          <v-divider class="my-6"></v-divider>

          <!-- Action Buttons -->
          <v-row>
            <v-col class="d-flex justify-end gap-3">
              <v-btn variant="outlined" color="grey-darken-1" :disabled="form.processing" @click="cancel">
                <v-icon icon="mdi-close" start></v-icon>
                Batal
              </v-btn>
              <v-btn type="submit" variant="flat" color="blue" :loading="form.processing" :disabled="form.processing">
                <v-icon icon="mdi-send" start></v-icon>
                Kirim
              </v-btn>
            </v-col>
          </v-row>
        </form>
      </v-col>
    </v-row>
  </v-container>
</template>

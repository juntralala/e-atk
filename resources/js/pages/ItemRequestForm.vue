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
  <v-container fluid>
    <SuccessDialog
      v-model="successDialog"
      message="Permintaan barang berhasil disimpan."
    />
    <AlertDialog
      title="Gagal!"
      v-model="errorDialog"
      :message="errorMessage"
    />
    
    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Minta"
          second-part-title="Barang"
        />
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <form @submit.prevent="submitRequest">
          <!-- Request Items -->
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
                :hint="item.item_id ? `${getItemFromProp(item.item_id)?.spesification_name} - Stok: ${getItemFromProp(item.item_id)?.stock} ${getItemFromProp(item.item_id)?.unit?.name}` : ''"
                persistent-hint
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
                v-model="item.requested_quantity"
                :min="1"
                control-variant="split"
                density="comfortable"
                label="Jumlah Diminta"
                :error-messages="form.errors[`items.${index}.requested_quantity`]"
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
                  icon="mdi-send"
                  start
                ></v-icon>
                Kirim Permintaan
              </v-btn>
            </v-col>
          </v-row>
        </form>
      </v-col>
    </v-row>
  </v-container>
</template>
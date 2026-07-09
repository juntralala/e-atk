<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
  reason: null,
});
const showCreateFormDialog = ref(false);

function closeCreateFormDialog() {
  showCreateFormDialog.value = false;
  form.reason = null;
}

function createStockAdjustmentReason() {
  form.post(route('stock.adjustments.reasons.create'), {
    preserveScroll: true,
    onSuccess() {
      closeCreateFormDialog();
    },
  });
}

function clearErrors() {
  form.clearErrors();
}
</script>

<template>
  <v-dialog v-model="showCreateFormDialog" activator="parent" class="max-w-200" v-slot="{ isActive }">
    <v-card>
      <v-card-title class="bg-blue-darken-2 text-center">Tambah Sebab Penyesuaian Stok</v-card-title>
      <v-form @submit.prevent="createStockAdjustmentReason" @input="clearErrors">
        <v-card-text>
          <v-text-field v-model="form.reason" label="Sebab" :error-messages="form.errors.reason" variant="outlined" />
        </v-card-text>
        <v-card-actions>
          <v-btn variant="tonal" color="blue" type="submit">Simpan</v-btn>
          <v-btn @click="isActive.value = false">Batal</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </v-dialog>
</template>

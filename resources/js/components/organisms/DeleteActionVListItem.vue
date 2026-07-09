<script setup>
import { router } from '@inertiajs/vue3';

const emit = defineEmits(['closeErrorDialog', 'openErrorDialog']);

const props = defineProps({
  deleteUrl: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
});

function handleDeleteClick(deleteUrl) {
  router.delete(deleteUrl, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload();
    },
    onError: (errors) => {
      emit('openErrorDialog', errors.message || `Terjadi kesalahan saat menghapus  ${props.name}.`);
      setTimeout(() => {
        emit('closeErrorDialog');
      }, 3000);
    },
  });
}
</script>

<template>
  <v-list-item value="delete">
    <v-icon icon="mdi-delete" class="mr-2" />
    Hapus
    <v-dialog v-slot="{ isActive }" activator="parent" max-width="400">
      <v-card>
        <v-card-title class="bg-blue-darken-2 text-center">Konfirmasi Hapus!</v-card-title>
        <v-card-text>
          Apakah yakin ingin menghapus <span class="font-weight-bold">{{ name }}</span
          >?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="isActive.value = false">Batal</v-btn>
          <v-btn
            color="error"
            @click="
              handleDeleteClick(deleteUrl);
              isActive.value = false;
            ">
            Hapus
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-list-item>
</template>

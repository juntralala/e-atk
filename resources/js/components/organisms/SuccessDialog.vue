<script setup>
defineProps({
  title: {
    type: String,
    default: 'Berhasil!',
  },
  message: {
    type: String,
    default: '',
  },
});
const show = defineModel();
</script>

<template>
  <v-dialog v-slot="{ isActive }" v-model="show" max-width="400">
    <v-card>
      <v-card-text class="pa-8 text-center">
        <v-icon icon="mdi-check-circle" size="64" color="success" class="mb-4"></v-icon>
        <h2 class="text-h5 font-weight-bold mb-2">{{ title }}</h2>
        <p class="text-body-1">{{ message }}</p>
      </v-card-text>
      <v-card-actions class="justify-center pb-6">
        <v-btn color="success" variant="flat" @click="isActive.value = false"> Tutup </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

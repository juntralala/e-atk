<script setup>
defineProps({
    url: {
        required: true,
        type: [String, null],
        default: null
    }
});
</script>

<template>
  <v-avatar>
    <img
      v-if="url"
      :src="url"
      alt="Foto Profil"
    />
    <v-icon
      v-else
      class="cursor-pointer"
      icon="mdi-account"
      size="x-large"
    />
  </v-avatar>
</template>

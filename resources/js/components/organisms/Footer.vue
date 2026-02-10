<script setup>
defineProps({
  namaInstansi: { type: String, default: 'Kosong' },
  alamatInstansi: { type: String, default: 'Kosong' },
});
</script>

<template>
  <v-footer
    app
    color="secondary"
    class="static! self-end py-2 max-h-15!"
  >
    <div class="w-full">
      <div class="flex flex-wrap justify-center items-center gap-4 mb-2">
        <div class="flex items-center gap-2">
          <v-icon size="small">mdi-map-marker</v-icon>
          <span class="text-sm">{{ alamatInstansi }}</span>
        </div>
        <v-divider vertical class="hidden md:block"></v-divider>
        <div class="flex items-center gap-2">
          <v-icon size="small">mdi-phone</v-icon>
          <span class="text-sm">{{ $page?.props?.settings?.institutionPhone }}</span>
        </div>
      </div>
      
      <p class="text-center text-xs opacity-80">
        ©2026 {{ namaInstansi }}. All rights reserved.
      </p>
    </div>
  </v-footer>
</template>
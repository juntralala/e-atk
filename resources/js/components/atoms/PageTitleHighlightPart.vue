<script setup>
// componen ini biar judul antar page konsisten
defineProps({
  firstPartTitle: String,
  secondPartTitle: String,
});
</script>
<template>
  <h1 class="text-4xl font-medium">
    {{ firstPartTitle }} <span class="text-blue-accent-3">{{ secondPartTitle }}</span>
  </h1>
</template>

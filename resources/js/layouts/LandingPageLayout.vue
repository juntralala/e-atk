<script setup>
import Footer from '@/components/organisms/Footer.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  settings: {
    type: [Object, null],
    default: null
  }
});
</script>

<template>
  <Head>
    <link
      rel="shortcut icon"
      :href="settings?.icon || 'favicon.ico'"
      type="image/x-icon"
    />
    <title>{{ settings?.applicationName }}</title>
  </Head>
  <v-app>
    <v-app-bar
      elevation="1" 
      color="blue-darken-2"
      class="pe-2"
    >
      <v-app-bar-title>
        <v-avatar v-if="settings?.icon" variant="text">
          <v-img :src="settings?.icon"/>
        </v-avatar>
        <span class="ms-2">{{ settings?.applicationName }}</span>
      </v-app-bar-title>
      <template #append>
        <Link :href="route('login')">
          <v-btn variant="outlined" elevation="0" class="font-bold!">Masuk</v-btn>
        </Link>
      </template>
    </v-app-bar>
    <v-main>
      <slot></slot>
    </v-main>

    <Footer :namaInstansi="settings?.institutionName" :alamatInstansi="settings?.institutionName"></Footer>
  </v-app>
</template>

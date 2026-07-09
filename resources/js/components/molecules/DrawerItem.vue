<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: {
    required: true,
    type: String,
  },
  icon: {
    required: false,
    type: String,
    default: null,
  },
});
</script>

<template>
  <Link :href="href">
    <v-list-item :value="href">
      <div class="flex items-baseline-last gap-1">
        <v-icon :icon="icon" />
        <div>
          <slot></slot>
        </div>
      </div>
    </v-list-item>
  </Link>
</template>

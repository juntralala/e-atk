<script setup>
import { ref } from 'vue';
const password = defineModel();
const showPassword = ref(false);
function toggleShowPassword() {
  showPassword.value = !showPassword.value;
}
</script>

<template>
  <v-text-field
    v-model="password"
    label="Password"
    :type="showPassword ? 'text' : 'password'"
    :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
    @click:append-inner="toggleShowPassword"></v-text-field>
</template>

<script setup>
import BubbleUpLayout from '@/layouts/BubbleUpLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
  layout: BubbleUpLayout
});

const form = useForm({
  username: null,
  password: null
});

const showPassword = ref(false);
function toggleShowPassword() {
  showPassword.value = !showPassword.value;
}
function login() {
  form.post('/login', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    },
  });
}
</script>

<template>
  <v-container class="h-dvh">
    <v-row class="fill-height" align="center">
      <v-col>
        <v-card class="ma-auto max-w-150!">
          <v-card-title class="text-center mt-5">
            <v-avatar v-if="$page.props.settings.app_icon" size="128" class="mb-4">
              <v-img :src="$page.props.settings.app_icon" alt="Icon Aplikasi" />
            </v-avatar>
            <div v-if="$page.props.settings.company_name" class="text-blue-darken-3 text-2xl font-semibold">{{
              $page.props.settings.company_name }}</div>
            <v-card-subtitle class="text-center">Kami Himung, Pian Wigas</v-card-subtitle>
            <!-- <div>Login</div> -->
          </v-card-title>
          <v-form @submit.prevent="login">
            <v-card-text>
              <v-text-field v-model="form.username" label="username"
                :error-messages="form.errors.username"></v-text-field>
              <v-text-field v-model="form.password" label="password" :type="showPassword ? 'text' : 'password'"
                :error-messages="form.errors.password" :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append-inner="toggleShowPassword"></v-text-field>
            </v-card-text>
            <v-card-actions class="justify-center mb-3">
              <v-btn color="blue-darken-3" width="100%" variant="flat" :loading="form.processing"
                :disabled="form.processing" type="submit">
                <span v-if="!form.processing">Login</span>
                <span v-else>
                  <v-progress-circular indeterminate />
                </span>
              </v-btn>
            </v-card-actions>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
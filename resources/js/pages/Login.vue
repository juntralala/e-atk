<script setup>
import PasswordInput from '@/components/atoms/PasswordInput.vue';
import BubbleUpLayout from '@/layouts/BubbleUpLayout.vue';
import { requestNotificationPermission } from '@/lib/notification';
import { validators } from '@/validators/validators';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
  layout: BubbleUpLayout,
});

defineProps({
  settings: {
    type: Object,
    default: null,
  },
});

const form = useForm({
  username: null,
  password: null,
});
const isFormValid = ref(false);

const validations = {
  username: [(v) => validators.required(v, 'Username')],
  password: [(v) => validators.required(v, 'Password')],
};

function login() {
  form.post('/login', {
    onSuccess: () => {
      form.reset();
      requestNotificationPermission();
    },
    onFinish: () => {
      form.password = '';
    },
  });
}
function clearErrorOnUsernamePasswordChange() {
  form.clearErrors('username');
  form.clearErrors('password');
}
</script>

<template>
  <v-container class="h-dvh">
    <v-row class="fill-height" align="center">
      <v-col>
        <v-card class="ma-auto max-w-150! px-5" rounded="xl  ">
          <v-card-title class="mt-5 text-center">
            <!-- <v-avatar v-if="settings?.icon"   class="mb-4" tile>
            </v-avatar> -->
            <v-img :src="settings?.icon" height="100" alt="Logo Aplikasi" />
            <div v-if="settings?.institutionName" class="text-blue-darken-3 text-2xl font-semibold">
              {{ settings?.institutionName }}
            </div>
            <v-card-subtitle class="text-center">Login</v-card-subtitle>
          </v-card-title>
          <v-form @submit.prevent="login" v-model="isFormValid">
            <v-card-text>
              <v-text-field
                v-model="form.username"
                :rules="validations.username"
                label="Username"
                :error-messages="form.errors.username"
                @input="clearErrorOnUsernamePasswordChange" />
              <PasswordInput
                v-model="form.password"
                :rules="validations.password"
                :error-message="form.errors.password"
                @input="clearErrorOnUsernamePasswordChange" />
            </v-card-text>
            <v-card-actions class="mb-3 justify-center">
              <v-btn
                color="blue-darken-3"
                width="100%"
                variant="flat"
                :loading="form.processing"
                :disabled="form.processing || !isFormValid"
                type="submit">
                Login
              </v-btn>
            </v-card-actions>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

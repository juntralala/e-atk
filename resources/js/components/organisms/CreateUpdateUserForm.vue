<script setup>
  import { validators } from '@/validators/validators';
  import { useForm } from '@inertiajs/vue3';
  import { onMounted, ref, computed } from 'vue';
  import AlertDialog from './AlertDialog.vue';

  const { mode, initialValue, url } = defineProps({
    title: {
      required: true,
      type: String,
    },
    url: {
      required: true,
      type: String,
    },
    mode: {
      type: String,
      default: 'create',
    },
    initialValue: {
      type: Object,
      default: {
        name: '',
        password: '',
        username: '',
        role: '',
        telepon: '',
      },
    },
    activator: {
      type: [String, Object],
    },
  });

  const formRef = ref(null);
  const isFormValid = ref(false);
  const form = useForm({
    name: initialValue.name,
    username: initialValue.username,
    password: initialValue.password,
    role: initialValue.role,
    telepon: initialValue.telepon,
  });
  const formDialog = ref(false);
  const showPassword = ref(false);
  const roles = ref([]);
  const errorMessage = ref("");
  const showAlert = ref(false);

  // Computed properties untuk menggabungkan validasi lokal dan error dari server
  const nameRules = computed(() => [
    (v) => validators.required(v, 'name'),
    (value) => value.length >= 4 || 'Nama harus setidaknya 4 karakter',
    (value) => value.length < 255 || 'Nama tidak boleh lebih 255 karakter',
    () => !form.errors.name || form.errors.name,
  ]);

  const usernameRules = computed(() => [
    validators.required,
    (value) => value.length >= 4 || 'Username harus setidaknya 4 karakter',
    (value) => value.length < 255 || 'Username tidak boleh lebih 255 karakter',
    (value) => !/^[0-9._]/.test(value) || 'Username hanya boleh diawali huruf',
    (value) => /^[A-Za-z0-9._]+$/.test(value) || 'Username hanya boleh huruf, nomer, titik dan _',
    () => !form.errors.username || form.errors.username,
  ]);

  const passwordRules = computed(() => [
    (value) => (mode == 'edit' ? true : validators.required(value, 'password')),
    (value) => !value || value.length >= 4 || 'Password harus setidaknya 4 karakter',
    (value) => !value || value.length < 60 || 'Password tidak boleh lebih 60 karakter',
    () => !form.errors.password || form.errors.password,
  ]);

  const roleRules = computed(() => [
    validators.required,
    () => !form.errors.role || form.errors.role,
  ]);

  async function submit() {
    const validated = await formRef.value.validate();
    if (validated.valid) {
      const options = {
        onSuccess: () => {
          formDialog.value = false;
          form.reset();
          form.clearErrors(); // Bersihkan error setelah sukses
        },
        onError: (errors) => {
          // Error dari field validasi akan otomatis muncul di form
          // Error general akan ditampilkan di alert dialog
          if (errors.message) {
            errorMessage.value = errors.message;
            showAlert.value = true;
          } else if (errors.error) {
            errorMessage.value = errors.error;
            showAlert.value = true;
          }
          
          // Trigger validasi ulang untuk menampilkan error dari server
          formRef.value.validate();
        },
      };

      if (mode === 'create') {
        form.post(url, options);
      } else if (mode === 'edit') {
        form.put(url, options);
      }
    }
  }

  async function fetchRoles() {
    return (await (await fetch('/roles')).json()).data;
  }

  onMounted(async () => {
    roles.value = await fetchRoles();
  });
</script>

<template>
  <AlertDialog v-model="showAlert" title="Error" :message="errorMessage"/>
  <v-expand-transition>
    <v-dialog
      v-slot="{ isActive }"
      v-model="formDialog"
      :activator="activator"
      max-width="800"
    >
      <v-card>
        <v-card-title class="text-center">{{ title }}</v-card-title>
        <v-divider />
        <v-card-text>
          <v-form
            ref="formRef"
            v-model="isFormValid"
            @submit.prevent="submit"
          >
            <v-text-field
              v-model="form.name"
              label="Nama"
              density="comfortable"
              :rules="nameRules"
              :error-messages="form.errors.name"
            />
            <v-text-field
              v-model="form.username"
              label="Username"
              density="comfortable"
              :rules="usernameRules"
              :error-messages="form.errors.username"
            />
            <v-text-field
              v-model="form.password"
              label="Password"
              density="comfortable"
              :rules="passwordRules"
              :error-messages="form.errors.password"
              :type="showPassword ? 'text' : 'password'"
              :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
              @click:append-inner="showPassword = !showPassword"
            />
            <v-select
              v-model="form.role"
              :items="roles"
              item-title="name"
              item-value="id"
              density="comfortable"
              :rules="roleRules"
              :error-messages="form.errors.role"
              label="Role"
            />
  
            <v-text-field
              v-model="form.telepon"
              label="No. Telepon"
              density="comfortable"
              :rules="[]"
              :error-messages="form.errors.telepon"
            />
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-btn @click="isActive.value = false">Batal</v-btn>
          <v-btn
            color="blue-darken-4"
            :disabled="form.processing || !isFormValid"
            :loading="form.processing"
            @click="submit"
          >
            Submit
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-expand-transition>
</template>
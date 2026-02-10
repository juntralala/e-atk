<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  settings: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  icon: null,
  logo: null,
  applicationName: props.settings.applicationName || '',
  institutionName: props.settings.institutionName || '',
  institutionAddress: props.settings.institutionAddress || '',
  institutionPhone: props.settings.institutionPhone || '',
});

const iconPreview = ref(props.settings.icon || null);
const logoPreview = ref(props.settings.logo || null);

const errorDialog = ref(false);
const errorMessage = ref('');
const successDialog = ref(false);

const handleIconChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.icon = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      iconPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleLogoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const submitForm = () => {
  form.put(`/settings`, {
    forceFormData: true,
    onSuccess: () => {
      successDialog.value = true;
      setTimeout(() => {
        successDialog.value = false;
        router.reload();
      }, 2000);
    },
    onError: (errors) => {
      // Cek apakah ada error non-field
      const fieldErrors = ['icon', 'logo', 'applicationName', 'institutionName', 'institutionAddress', 'institutionPhone'];
      const hasNonFieldError = Object.keys(errors).some(key => !fieldErrors.includes(key));
      
      if (hasNonFieldError || errors.message) {
        errorMessage.value = errors.message || 'Terjadi kesalahan saat memperbarui pengaturan.';
        errorDialog.value = true;
        
        setTimeout(() => {
          errorDialog.value = false;
          errorMessage.value = '';
        }, 3000);
      }
    },
  });
};

const closeErrorDialog = () => {
  errorDialog.value = false;
  errorMessage.value = '';
};
</script>

<template>
  <Head>
    <title>Pengaturan</title>
  </Head>
  <v-container>
     <SuccessDialog v-model="successDialog" title="Berhasil!" message="Pengaturan berhasil diperbarui."/>
     <AlertDialog v-model="errorDialog" title="Gagal!" :message="errorMessage"/>

    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Pengaturan"
          second-part-title="Aplikasi"
        />
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title class="bg-blue-darken-2">
            <span class="text-h6">Edit Pengaturan Aplikasi</span>
          </v-card-title>
          <v-card-text class="pt-6">
            <v-form @submit.prevent="submitForm">
              <v-row>
                <!-- Icon Upload -->
                <v-col cols="12" md="6">
                  <v-label class="mb-2 font-weight-bold">Icon Aplikasi</v-label>
                  <v-file-input
                    label="Pilih Icon"
                    accept="image/*"
                    prepend-icon="mdi-image"
                    :error-messages="form.errors.icon"
                    variant="outlined"
                    @change="handleIconChange"
                  ></v-file-input>
                  <v-img
                    v-if="iconPreview"
                    :src="iconPreview"
                    max-width="150"
                    max-height="150"
                    class="mt-2 border"
                  ></v-img>
                </v-col>

                <!-- Logo Upload -->
                <v-col cols="12" md="6">
                  <v-label class="mb-2 font-weight-bold">Logo Aplikasi</v-label>
                  <v-file-input
                    label="Pilih Logo"
                    accept="image/*"
                    prepend-icon="mdi-image"
                    :error-messages="form.errors.logo"
                    variant="outlined"
                    @change="handleLogoChange"
                  ></v-file-input>
                  <v-img
                    v-if="logoPreview"
                    :src="logoPreview"
                    max-width="200"
                    max-height="150"
                    class="mt-2 border"
                  ></v-img>
                </v-col>

                <!-- Nama Aplikasi -->
                <v-col cols="12">
                  <v-text-field
                    v-model="form.applicationName"
                    label="Nama Aplikasi"
                    :error-messages="form.errors.applicationName"
                    placeholder="Masukkan nama aplikasi"
                    prepend-inner-icon="mdi-application"
                    required
                    variant="outlined"
                  ></v-text-field>
                </v-col>

                <!-- Nama Instansi -->
                <v-col cols="12">
                  <v-text-field
                    v-model="form.institutionName"
                    label="Nama Instansi"
                    :error-messages="form.errors.institutionName"
                    placeholder="Masukkan nama instansi"
                    prepend-inner-icon="mdi-office-building"
                    required
                    variant="outlined"
                  ></v-text-field>
                </v-col>

                <!-- Alamat Instansi -->
                <v-col cols="12">
                  <v-textarea
                    v-model="form.institutionAddress"
                    label="Alamat Instansi"
                    :error-messages="form.errors.institutionAddress"
                    placeholder="Masukkan alamat lengkap instansi"
                    prepend-inner-icon="mdi-map-marker"
                    rows="3"
                    required
                    variant="outlined"
                  ></v-textarea>
                </v-col>

                <!-- Telepon Instansi -->
                <v-col cols="12">
                  <v-text-field
                    v-model="form.institutionPhone"
                    label="Telepon Instansi"
                    :error-messages="form.errors.institutionPhone"
                    placeholder="Masukkan nomor telepon instansi"
                    prepend-inner-icon="mdi-phone"
                    required
                    variant="outlined"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>

          <v-card-actions class="px-6 pb-6">
            <v-spacer></v-spacer>
            <v-btn
              color="grey-darken-1"
              variant="text"
              :disabled="form.processing"
              @click="router.visit('home')"
            >
              Batal
            </v-btn>
            <v-btn
              color="primary"
              variant="tonal"
              :loading="form.processing"
              @click="submitForm"
            >
              Simpan Perubahan
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
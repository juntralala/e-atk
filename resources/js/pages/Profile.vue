<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const page = usePage();
const { user } = page.props.auth;

const profile = ref({
  name: user.name,
  username: user.username,
  password: '********',
  role: user.role.name,
  photo: user.profile_photo_path,
  telepon: user.telepon,
});

const isEditing = ref(false);
const showPassword = ref(false);
const fileInput = ref(null);
const processing = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const editForm = ref({
  name: '',
  username: '',
  password: '',
  password_confirmation: '',
  photo: null,
  photoFile: null,
  photoPreview: null,
  telepon: null,
});

// Validation rules
const validationErrors = ref({});

const validateForm = () => {
  validationErrors.value = {};
  let isValid = true;

  // Name validation
  if (!editForm.value.name || editForm.value.name.trim().length < 3) {
    validationErrors.value.name = 'Nama minimal 3 karakter';
    isValid = false;
  }

  // Username validation (alphanumeric and underscore only)
  const usernameRegex = /^[a-zA-Z0-9_.]{4,100}$/;
  if (!usernameRegex.test(editForm.value.username)) {
    validationErrors.value.username = 'Username 4-100 karakter (huruf, angka, underscore dan titik)';
    isValid = false;
  }

  // Password validation (only if password is being changed)
  if (editForm.value.password) {
    if (editForm.value.password.length < 4) {
      validationErrors.value.password = 'Password minimal 4 karakter';
      isValid = false;
    }

    if (editForm.value.password !== editForm.value.password_confirmation) {
      validationErrors.value.password_confirmation = 'Konfirmasi password tidak cocok';
      isValid = false;
    }
  }

  if(editForm.value.telepon?.length < 11) {
    validationErrors.value.telepon = "Nomer telepon terlalu pendek";
    isValid = false;

  }
  if(!validationErrors.value.telepon && !/^08[0-9]+$/.test(editForm.value.telepon)) {
    validationErrors.value.telepon = "Format nomer telepon tidak valid";
    isValid = false;
  }

  // Photo validation
  if (editForm.value.photoFile) {
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(editForm.value.photoFile.type)) {
      validationErrors.value.photo = 'Format foto harus JPG, PNG, atau WEBP';
      isValid = false;
    }

    if (editForm.value.photoFile.size > maxSize) {
      validationErrors.value.photo = 'Ukuran foto maksimal 2MB';
      isValid = false;
    }
  }

  return isValid;
};

const startEdit = () => {
  editForm.value = {
    name: profile.value.name,
    username: profile.value.username,
    password: '',
    password_confirmation: '',
    photo: profile.value.photo,
    photoFile: null,
    photoPreview: null,
    telepon: profile.value.telepon,
  };
  isEditing.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  validationErrors.value = {};
};

const cancelEdit = () => {
  isEditing.value = false;
  editForm.value = {
    name: '',
    username: '',
    password: '',
    password_confirmation: '',
    photo: '',
    photoFile: null,
    photoPreview: '',
    telepon: '',
  };
  errorMessage.value = '';
  successMessage.value = '';
  validationErrors.value = {};
};

const saveChanges = async () => {
  if (!validateForm()) {
    errorMessage.value = 'Mohon perbaiki kesalahan pada form';
    return;
  }

  processing.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const formData = {
      _method: 'PUT',
      name: editForm.value.name.trim(),
      username: editForm.value.username.trim(),
      telepon: editForm.value.telepon.trim(),
    };

    // Only include password if it's being changed
    if (editForm.value.password) {
      formData.password = editForm.value.password;
      formData.password_confirmation = editForm.value.password_confirmation;
    }

    // Include photo if changed - pastikan file object yang valid
    if (editForm.value.photoFile && editForm.value.photoFile instanceof File) {
      formData.profilePhoto = editForm.value.photoFile;
      console.log('Photo file:', {
        name: editForm.value.photoFile.name,
        type: editForm.value.photoFile.type,
        size: editForm.value.photoFile.size,
      });
    }

    // Use Inertia's router.post with forceFormData
    router.post(route('profile.update', user.id), formData, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: (page) => {
        const updatedUser = page.props.auth?.user;

        if (updatedUser) {
          profile.value.name = updatedUser.name;
          profile.value.username = updatedUser.username;
          profile.value.telepon = updatedUser.telepon;

          if (updatedUser.profile_photo_path) {
            profile.value.photo = updatedUser.profile_photo_path;
          }

          if (editForm.value.password) {
            profile.value.password = '********';
          }
        }

        successMessage.value = 'Profil berhasil diperbarui';
        isEditing.value = false;
        validationErrors.value = {};

        // Reset form
        editForm.value.photoFile = null;
        editForm.value.photoPreview = null;

        setTimeout(() => {
          successMessage.value = '';
        }, 5000);
      },
      onError: (errors) => {
        console.error('Validation errors:', errors);

        // Handle Laravel validation errors
        if (errors) {
          validationErrors.value = errors;
          errorMessage.value = 'Terdapat kesalahan validasi. Mohon periksa form.';
        } else {
          errorMessage.value = 'Gagal memperbarui profil. Silakan coba lagi.';
        }
      },
      onFinish: () => {
        processing.value = false;
      },
    });
  } catch (error) {
    console.error('Error updating profile:', error);
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.';
    processing.value = false;
  }
};

const handlePhotoClick = () => {
  if (isEditing.value && fileInput.value) {
    fileInput.value.click();
  }
};

const handleFileChange = (event) => {
  const file = event.target.files[0];

  if (!file) return;

  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    validationErrors.value.photo = 'Format foto harus JPG, PNG, atau WEBP';
    return;
  }

  // Validate file size (2MB max)
  const maxSize = 2 * 1024 * 1024;
  if (file.size > maxSize) {
    validationErrors.value.photo = 'Ukuran foto maksimal 2MB';
    return;
  }

  delete validationErrors.value.photo;
  editForm.value.photoFile = file;

  const reader = new FileReader();
  reader.onload = (e) => {
    editForm.value.photoPreview = e.target.result;
  };
  reader.onerror = () => {
    validationErrors.value.photo = 'Gagal membaca file';
  };
  reader.readAsDataURL(file);
};

const removePhoto = () => {
  editForm.value.photoFile = null;
  editForm.value.photoPreview = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
  delete validationErrors.value.photo;
};

const displayPhoto = computed(() => {
  if (isEditing.value) {
    return editForm.value.photoPreview || editForm.value.photo;
  }
  return profile.value.photo;
});
</script>

<template>
  <v-container class="py-8">
    <v-row>
      <v-col cols="12">
        <div class="mb-8">
          <PageTitleHighlightPart
            first-part-title="Profil"
            second-part-title="Pengguna"
          />
          <div class="text-subtitle-1 text-grey">Kelola detail akunmu</div>
        </div>

        <!-- Alert Messages -->
        <v-alert
          v-if="successMessage"
          type="success"
          class="mb-4"
          closable
          @click:close="successMessage = ''"
        >
          {{ successMessage }}
        </v-alert>

        <v-alert
          v-if="errorMessage"
          type="error"
          class="mb-4"
          closable
          @click:close="errorMessage = ''"
        >
          {{ errorMessage }}
        </v-alert>

        <div>
          <div class="mb-8">
            <v-avatar
              size="150"
              class="mb-4 bg-gray-200!"
              :style="isEditing ? 'cursor: pointer;' : ''"
              @click="handlePhotoClick"
            >
              <v-img
                v-if="displayPhoto"
                :src="displayPhoto"
                alt="Profile Photo"
                cover
              />
              <v-icon
                v-else
                size="100"
                class="text-grey-lighten-1"
              >
                mdi-account
              </v-icon>
            </v-avatar>
            <input
              ref="fileInput"
              type="file"
              accept="image/jpeg,image/jpg,image/png,image/webp"
              style="display: none"
              @change="handleFileChange"
            />
            <div
              v-if="isEditing"
              class="mb-2"
            >
              <div class="text-caption text-grey mb-2"><span class="font-semibold text-gray-500">Klik foto</span> untuk mengganti</div>
              <v-btn
                v-if="editForm.photoPreview"
                size="small"
                color="error"
                variant="text"
                @click="removePhoto"
              >
                Hapus Foto Baru
              </v-btn>
            </div>
            <div
              v-if="validationErrors.photo"
              class="text-error text-caption"
            >
              {{ validationErrors.photo }}
            </div>
          </div>

          <!-- View Mode -->
          <div v-if="!isEditing">
            <v-row class="mb-4">
              <v-col cols="12">
                <div class="text-subtitle-2 text-grey mb-1">Nama</div>
                <div class="text-body-1">{{ profile.name }}</div>
              </v-col>
            </v-row>

            <v-row class="mb-4">
              <v-col cols="12">
                <div class="text-subtitle-2 text-grey mb-1">Username</div>
                <div class="text-body-1">{{ profile.username }}</div>
              </v-col>
            </v-row>

            <v-row class="mb-4">
              <v-col cols="12">
                <div class="text-subtitle-2 text-grey mb-1">Password</div>
                <div class="text-body-1">{{ profile.password }}</div>
              </v-col>
            </v-row>

            <v-row class="mb-4">
              <v-col cols="12">
                <div class="text-subtitle-2 text-grey mb-1">Nomer Telepon</div>
                <div class="text-body-1">{{ profile.telepon || "-" }}</div>
              </v-col>
            </v-row>

            <v-row class="mb-6">
              <v-col cols="12">
                <div class="text-subtitle-2 text-grey mb-1">Role</div>
                <v-chip
                  color="primary"
                  size="small"
                >
                  {{ profile.role }}
                </v-chip>
              </v-col>
            </v-row>
          </div>

          <!-- Edit Mode -->
          <div v-else>
            <v-form @submit.prevent="saveChanges">
              <v-text-field
                v-model="editForm.name"
                label="Nama *"
                variant="outlined"
                density="comfortable"
                class="mb-4"
                :disabled="processing"
                :error-messages="validationErrors.name"
              />

              <v-text-field
                v-model="editForm.username"
                label="Username *"
                variant="outlined"
                density="comfortable"
                class="mb-4"
                :disabled="processing"
                :error-messages="validationErrors.username"
                hint="Hanya huruf, angka, dan underscore (3-20 karakter)"
                persistent-hint
              />

              <v-text-field
                v-model="editForm.password"
                label="Password Baru"
                :type="showPassword ? 'text' : 'password'"
                variant="outlined"
                density="comfortable"
                class="mb-4"
                hint="Minimal 8 karakter. Biarkan kosong jika tidak ingin mengubah password"
                persistent-hint
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                :disabled="processing"
                :error-messages="validationErrors.password"
                @click:append-inner="showPassword = !showPassword"
              />

              <v-text-field
                v-if="editForm.password"
                v-model="editForm.password_confirmation"
                label="Konfirmasi Password Baru *"
                :type="showPassword ? 'text' : 'password'"
                variant="outlined"
                density="comfortable"
                class="mb-4"
                :disabled="processing"
                :error-messages="validationErrors.password_confirmation"
              />

              <v-text-field
                v-model="editForm.telepon"
                label="Nomer Telepon"
                variant="outlined"
                density="comfortable"
                class="mb-4"
                :disabled="processing"
                :error-messages="validationErrors.telepon"
                persistent-hint
              />

              <v-row class="mb-6">
                <v-col cols="12">
                  <div class="text-subtitle-2 text-grey mb-1">Role</div>
                  <v-chip
                    color="primary"
                    size="small"
                  >
                    {{ profile.role }}
                  </v-chip>
                </v-col>
              </v-row>
            </v-form>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex ga-2">
            <v-btn
              v-if="!isEditing"
              color="primary"
              variant="elevated"
              @click="startEdit"
            >
              <v-icon start>mdi-pencil</v-icon>
              Edit
            </v-btn>
            <template v-else>
              <v-btn
                variant="outlined"
                :disabled="processing"
                @click="cancelEdit"
              >
                <v-icon start>mdi-close</v-icon>
                Batal
              </v-btn>
              <v-btn
                color="primary"
                variant="elevated"
                :loading="processing"
                :disabled="processing"
                @click="saveChanges"
              >
                <v-icon start>mdi-content-save</v-icon>
                Simpan
              </v-btn>
            </template>
          </div>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.text-error {
  color: rgb(var(--v-theme-error));
}
</style>

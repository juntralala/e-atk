<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
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

const validationErrors = ref({});

const validateForm = () => {
  validationErrors.value = {};
  let isValid = true;

  if (!editForm.value.name || editForm.value.name.trim().length < 3) {
    validationErrors.value.name = 'Nama minimal 3 karakter';
    isValid = false;
  }

  const usernameRegex = /^[a-zA-Z0-9_.]{4,100}$/;
  if (!usernameRegex.test(editForm.value.username)) {
    validationErrors.value.username = 'Username 4-100 karakter (huruf, angka, underscore dan titik)';
    isValid = false;
  }

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

  if (editForm.value.telepon?.length < 11) {
    validationErrors.value.telepon = 'Nomer telepon terlalu pendek';
    isValid = false;
  }
  if (!validationErrors.value.telepon && !/^08[0-9]+$/.test(editForm.value.telepon)) {
    validationErrors.value.telepon = 'Format nomer telepon tidak valid';
    isValid = false;
  }

  if (editForm.value.photoFile) {
    const maxSize = 2 * 1024 * 1024;
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

    if (editForm.value.password) {
      formData.password = editForm.value.password;
      formData.password_confirmation = editForm.value.password_confirmation;
    }

    if (editForm.value.photoFile && editForm.value.photoFile instanceof File) {
      formData.profilePhoto = editForm.value.photoFile;
    }

    router.post(route('profile.update', user.id), formData, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: (page) => {
        const updatedUser = page.props.auth?.user;
        if (updatedUser) {
          profile.value.name = updatedUser.name;
          profile.value.username = updatedUser.username;
          profile.value.telepon = updatedUser.telepon;
          if (updatedUser.profile_photo_path) profile.value.photo = updatedUser.profile_photo_path;
          if (editForm.value.password) profile.value.password = '********';
        }
        successMessage.value = 'Profil berhasil diperbarui';
        isEditing.value = false;
        validationErrors.value = {};
        editForm.value.photoFile = null;
        editForm.value.photoPreview = null;
        setTimeout(() => {
          successMessage.value = '';
        }, 5000);
      },
      onError: (errors) => {
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
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.';
    processing.value = false;
  }
};

const handlePhotoClick = () => {
  if (isEditing.value && fileInput.value) fileInput.value.click();
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    validationErrors.value.photo = 'Format foto harus JPG, PNG, atau WEBP';
    return;
  }
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
  if (fileInput.value) fileInput.value.value = '';
  delete validationErrors.value.photo;
};

const displayPhoto = computed(() => {
  if (isEditing.value) return editForm.value.photoPreview || editForm.value.photo;
  return profile.value.photo;
});

const userInitials = computed(() => {
  const name = profile.value.name || '';
  return name
    .split(' ')
    .map((w) => w[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();
});
</script>

<template>
  <Head><title>Profil Pengguna</title></Head>

  <div class="profile-root">
    <v-container class="profile-container py-10">
      <!-- Page Header -->
      <div class="page-header mb-8">
        <p class="page-eyebrow">Akun Saya</p>
        <h1 class="page-title">Profil <span class="title-highlight">Pengguna</span></h1>
        <p class="page-sub">Kelola informasi akunmu</p>
      </div>

      <!-- Alerts -->
      <transition name="fade-slide">
        <div v-if="successMessage" class="alert alert-success mb-5">
          <v-icon size="18" class="mr-2">mdi-check-circle-outline</v-icon>
          {{ successMessage }}
          <button class="alert-x" @click="successMessage = ''"><v-icon size="16">mdi-close</v-icon></button>
        </div>
      </transition>
      <transition name="fade-slide">
        <div v-if="errorMessage" class="alert alert-error mb-5">
          <v-icon size="18" class="mr-2">mdi-alert-circle-outline</v-icon>
          {{ errorMessage }}
          <button class="alert-x" @click="errorMessage = ''"><v-icon size="16">mdi-close</v-icon></button>
        </div>
      </transition>

      <v-row>
        <!-- LEFT PANEL -->
        <v-col cols="12" md="4" lg="3">
          <div class="left-panel">
            <!-- Avatar -->
            <div class="avatar-zone" :class="{ 'avatar-zone--editable': isEditing }" @click="handlePhotoClick">
              <div class="avatar-circle">
                <img v-if="displayPhoto" :src="displayPhoto" alt="Photo" class="avatar-img" />
                <span v-else class="avatar-initials">{{ userInitials }}</span>
              </div>
              <div v-if="isEditing" class="avatar-hover-mask">
                <v-icon color="white" size="22">mdi-camera-outline</v-icon>
                <span>Ubah foto</span>
              </div>
            </div>

            <input
              ref="fileInput"
              type="file"
              accept="image/jpeg,image/jpg,image/png,image/webp"
              style="display: none"
              @change="handleFileChange" />

            <div v-if="isEditing && editForm.photoPreview" class="remove-photo" @click="removePhoto">
              <v-icon size="13">mdi-trash-can-outline</v-icon> Hapus foto baru
            </div>
            <p v-if="validationErrors.photo" class="err-text mt-1">{{ validationErrors.photo }}</p>

            <!-- Identity -->
            <div class="identity">
              <p class="identity-name">{{ profile.name }}</p>
              <p class="identity-username">{{ profile.username }}</p>
              <div class="role-pill"><span class="role-dot"></span>{{ profile.role }}</div>
            </div>

            <!-- Edit button on view mode -->
            <button v-if="!isEditing" class="btn-edit-main" @click="startEdit">
              <v-icon size="15">mdi-pencil-outline</v-icon>
              Edit Profil
            </button>
          </div>
        </v-col>

        <!-- RIGHT PANEL -->
        <v-col cols="12" md="8" lg="9">
          <div class="right-panel">
            <div class="panel-header">
              <span class="panel-title">Detail Akun</span>
              <div v-if="isEditing" class="edit-badge"><v-icon size="13">mdi-pencil</v-icon> Mode Edit</div>
            </div>

            <!-- VIEW MODE -->
            <div v-if="!isEditing" class="view-fields">
              <div class="field-item">
                <div class="field-icon"><v-icon size="16" color="#2563eb">mdi-account-outline</v-icon></div>
                <div>
                  <p class="field-label">Nama</p>
                  <p class="field-val">{{ profile.name }}</p>
                </div>
              </div>
              <div class="field-sep"></div>

              <div class="field-item">
                <div class="field-icon"><v-icon size="16" color="#2563eb">mdi-at</v-icon></div>
                <div>
                  <p class="field-label">Username</p>
                  <p class="field-val">{{ profile.username }}</p>
                </div>
              </div>
              <div class="field-sep"></div>

              <div class="field-item">
                <div class="field-icon"><v-icon size="16" color="#2563eb">mdi-lock-outline</v-icon></div>
                <div>
                  <p class="field-label">Password</p>
                  <p class="field-val pw-mask">{{ profile.password }}</p>
                </div>
              </div>
              <div class="field-sep"></div>

              <div class="field-item">
                <div class="field-icon"><v-icon size="16" color="#2563eb">mdi-phone-outline</v-icon></div>
                <div>
                  <p class="field-label">Nomer Telepon</p>
                  <p class="field-val" :class="{ 'field-empty': !profile.telepon }">
                    {{ profile.telepon || 'Belum diisi' }}
                  </p>
                </div>
              </div>
              <div class="field-sep"></div>

              <div class="field-item">
                <div class="field-icon"><v-icon size="16" color="#2563eb">mdi-shield-account-outline</v-icon></div>
                <div>
                  <p class="field-label">Role</p>
                  <div class="role-pill mt-1"><span class="role-dot"></span>{{ profile.role }}</div>
                </div>
              </div>
            </div>

            <!-- EDIT MODE -->
            <div v-else class="edit-fields">
              <div class="input-wrap">
                <label class="input-label">
                  <v-icon size="13" color="#64748b">mdi-account-outline</v-icon>
                  Nama Lengkap <span class="req">*</span>
                </label>
                <input
                  v-model="editForm.name"
                  class="inp"
                  :class="{ 'inp--err': validationErrors.name }"
                  :disabled="processing"
                  placeholder="Nama lengkap" />
                <p v-if="validationErrors.name" class="err-text">{{ validationErrors.name }}</p>
              </div>

              <div class="input-wrap">
                <label class="input-label">
                  <v-icon size="13" color="#64748b">mdi-at</v-icon>
                  Username <span class="req">*</span>
                </label>
                <input
                  v-model="editForm.username"
                  class="inp"
                  :class="{ 'inp--err': validationErrors.username }"
                  :disabled="processing"
                  placeholder="username_kamu" />
                <p v-if="validationErrors.username" class="err-text">{{ validationErrors.username }}</p>
                <p class="hint-text">Huruf, angka, underscore dan titik (4–100 karakter)</p>
              </div>

              <div class="input-wrap">
                <label class="input-label">
                  <v-icon size="13" color="#64748b">mdi-lock-outline</v-icon>
                  Password Baru
                </label>
                <div class="inp-relative">
                  <input
                    v-model="editForm.password"
                    :type="showPassword ? 'text' : 'password'"
                    class="inp inp--padded"
                    :class="{ 'inp--err': validationErrors.password }"
                    :disabled="processing"
                    placeholder="Kosongkan jika tidak diubah" />
                  <button type="button" class="eye-btn" @click="showPassword = !showPassword">
                    <v-icon size="17" color="#94a3b8">{{
                      showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'
                    }}</v-icon>
                  </button>
                </div>
                <p v-if="validationErrors.password" class="err-text">{{ validationErrors.password }}</p>
                <p class="hint-text">Minimal 4 karakter</p>
              </div>

              <div v-if="editForm.password" class="input-wrap">
                <label class="input-label">
                  <v-icon size="13" color="#64748b">mdi-lock-check-outline</v-icon>
                  Konfirmasi Password <span class="req">*</span>
                </label>
                <input
                  v-model="editForm.password_confirmation"
                  :type="showPassword ? 'text' : 'password'"
                  class="inp"
                  :class="{ 'inp--err': validationErrors.password_confirmation }"
                  :disabled="processing"
                  placeholder="Ulangi password baru" />
                <p v-if="validationErrors.password_confirmation" class="err-text">
                  {{ validationErrors.password_confirmation }}
                </p>
              </div>

              <div class="input-wrap">
                <label class="input-label">
                  <v-icon size="13" color="#64748b">mdi-phone-outline</v-icon>
                  Nomer Telepon
                </label>
                <input
                  v-model="editForm.telepon"
                  class="inp"
                  :class="{ 'inp--err': validationErrors.telepon }"
                  :disabled="processing"
                  placeholder="08xxxxxxxxxx" />
                <p v-if="validationErrors.telepon" class="err-text">{{ validationErrors.telepon }}</p>
              </div>

              <div class="input-wrap">
                <label class="input-label">
                  <v-icon size="13" color="#64748b">mdi-shield-account-outline</v-icon>
                  Role
                </label>
                <div class="role-pill mt-1"><span class="role-dot"></span>{{ profile.role }}</div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="isEditing" class="action-row">
              <button class="btn btn-ghost" :disabled="processing" @click="cancelEdit">
                <v-icon size="15">mdi-close</v-icon> Batal
              </button>
              <button class="btn btn-save" :disabled="processing" @click="saveChanges">
                <v-progress-circular v-if="processing" indeterminate size="14" width="2" color="white" class="mr-1" />
                <v-icon v-else size="15">mdi-content-save-outline</v-icon>
                {{ processing ? 'Menyimpan…' : 'Simpan Perubahan' }}
              </button>
            </div>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<style scoped>
/* @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap'); */

.profile-root {
  min-height: 100vh;
  background: #f0f4ff;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.profile-container {
  max-width: 1000px !important;
}

/* HEADER */
.page-header {
  animation: fadeUp 0.5s ease both;
}
.page-eyebrow {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #3b82f6;
  margin: 0 0 6px;
}
.page-title {
  font-size: clamp(1.7rem, 4vw, 2.5rem);
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 6px;
  line-height: 1.15;
}
.title-highlight {
  color: #2563eb;
}
.page-sub {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

/* ALERTS */
.alert {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 13.5px;
  font-weight: 500;
}
.alert-success {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}
.alert-error {
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}
.alert-x {
  margin-left: auto;
  background: none;
  border: none;
  cursor: pointer;
  opacity: 0.5;
  color: inherit;
  display: flex;
  align-items: center;
}
.alert-x:hover {
  opacity: 1;
}

/* LEFT PANEL */
.left-panel {
  background: white;
  border-radius: 20px;
  padding: 32px 24px;
  text-align: center;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 16px rgba(37, 99, 235, 0.06);
  animation: fadeUp 0.5s ease 0.08s both;
  position: sticky;
  top: 24px;
}

/* AVATAR */
.avatar-zone {
  position: relative;
  width: 110px;
  height: 110px;
  margin: 0 auto 16px;
  cursor: default;
  border-radius: 50%;
}
.avatar-zone--editable {
  cursor: pointer;
}

.avatar-circle {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  overflow: hidden;
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid rgba(37, 99, 235, 0.12);
}
.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.avatar-initials {
  font-size: 36px;
  font-weight: 700;
  color: #2563eb;
}

.avatar-hover-mask {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: rgba(37, 99, 235, 0.72);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  color: white;
  font-size: 11px;
  font-weight: 600;
  opacity: 0;
  transition: opacity 0.2s ease;
}
.avatar-zone--editable:hover .avatar-hover-mask {
  opacity: 1;
}

.remove-photo {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #ef4444;
  cursor: pointer;
  margin-top: 8px;
  padding: 3px 10px;
  border-radius: 20px;
  border: 1px solid #fecaca;
  background: #fef2f2;
  transition: background 0.15s;
}
.remove-photo:hover {
  background: #fee2e2;
}

/* IDENTITY */
.identity {
  margin-bottom: 20px;
}
.identity-name {
  font-size: 17px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 2px;
}
.identity-username {
  font-size: 13px;
  color: #94a3b8;
  margin: 0 0 12px;
}

.role-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  background: #eff6ff;
  color: #1d4ed8;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #bfdbfe;
}
.role-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #2563eb;
  animation: blink 2s ease infinite;
}
@keyframes blink {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.35;
  }
}

.btn-edit-main {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 100%;
  padding: 9px 20px;
  border-radius: 10px;
  background: #2563eb;
  color: white;
  font-family: inherit;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition:
    background 0.2s,
    transform 0.15s;
}
.btn-edit-main:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
}

/* RIGHT PANEL */
.right-panel {
  background: white;
  border-radius: 20px;
  padding: 32px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 16px rgba(37, 99, 235, 0.06);
  animation: fadeUp 0.5s ease 0.14s both;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f1f5f9;
}
.panel-title {
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.2px;
  color: #94a3b8;
}
.edit-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
  color: #2563eb;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  padding: 3px 10px;
  border-radius: 20px;
}

/* VIEW FIELDS */
.view-fields {
  display: flex;
  flex-direction: column;
}
.field-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 16px 4px;
}
.field-icon {
  width: 32px;
  height: 32px;
  border-radius: 9px;
  background: #eff6ff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 1px;
}
.field-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #94a3b8;
  margin: 0 0 3px;
}
.field-val {
  font-size: 15px;
  font-weight: 500;
  color: #0f172a;
  margin: 0;
}
.field-val.pw-mask {
  letter-spacing: 3px;
  color: #64748b;
}
.field-val.field-empty {
  color: #cbd5e1;
  font-style: italic;
}
.field-sep {
  height: 1px;
  background: #f8fafc;
  margin: 0 4px;
}

/* EDIT FIELDS */
.edit-fields {
  display: flex;
  flex-direction: column;
}
.input-wrap {
  margin-bottom: 20px;
}
.input-label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #64748b;
  margin-bottom: 7px;
}
.req {
  color: #3b82f6;
}
.inp {
  width: 100%;
  padding: 11px 14px;
  border-radius: 11px;
  border: 1.5px solid #e2e8f0;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 14px;
  color: #0f172a;
  background: #f8fafc;
  outline: none;
  transition:
    border-color 0.18s,
    box-shadow 0.18s,
    background 0.18s;
}
.inp:focus {
  border-color: #3b82f6;
  background: white;
  box-shadow: 0 0 0 3px rgba(191, 219, 254, 0.5);
}
.inp--err {
  border-color: #fca5a5;
  background: #fef2f2;
}
.inp:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
.inp--padded {
  padding-right: 44px;
}

.inp-relative {
  position: relative;
}
.eye-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 2px;
  transition: opacity 0.15s;
}
.eye-btn:hover {
  opacity: 0.7;
}

.err-text {
  font-size: 12px;
  color: #ef4444;
  font-weight: 500;
  margin: 5px 0 0;
}
.hint-text {
  font-size: 12px;
  color: #94a3b8;
  margin: 5px 0 0;
}

/* ACTIONS */
.action-row {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 8px;
  padding-top: 24px;
  border-top: 1px solid #f1f5f9;
}
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 22px;
  border-radius: 11px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.18s ease;
}
.btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
.btn-ghost {
  background: #f8fafc;
  color: #64748b;
  border: 1.5px solid #e2e8f0;
}
.btn-ghost:hover:not(:disabled) {
  background: #f1f5f9;
}
.btn-save {
  background: #2563eb;
  color: white;
  box-shadow: 0 2px 12px rgba(37, 99, 235, 0.28);
}
.btn-save:hover:not(:disabled) {
  background: #1d4ed8;
  box-shadow: 0 4px 16px rgba(37, 99, 235, 0.38);
  transform: translateY(-1px);
}
.btn-save:active:not(:disabled) {
  transform: translateY(0);
}

/* ANIMATIONS */
@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(14px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.25s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>

<script setup>
import LandingPageLayout from '@/layouts/LandingPageLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({
  layout: LandingPageLayout,
});

defineProps({
  settings: {
    type: Object,
    default: () => ({
      namaInstansi: 'Nama Instansi',
      logo: null,
      moto: 'Moto Instansi',
      visi: 'Visi instansi akan ditampilkan di sini',
      misi: ['Misi pertama instansi', 'Misi kedua instansi', 'Misi ketiga instansi'],
    }),
  },
  strukturOrganisasi: {
    type: Array,
    default: () => [
      {
        nama: 'Nama Pejabat',
        jabatan: 'Kepala Instansi',
        foto: null,
      },
      {
        nama: 'Nama Pejabat 2',
        jabatan: 'Sekretaris',
        foto: null,
      },
      {
        nama: 'Nama Pejabat 3',
        jabatan: 'Kepala Bidang',
        foto: null,
      },
    ],
  },
  stakeholders: {
    type: Array,
    default: () => [
      {
        nama: 'Bendahara',
        deskripsi: 'Mengakses laporan keuangan',
        icon: 'mdi-account-tie',
      },
      {
        nama: 'Petugas',
        deskripsi: 'Menginput dan memproses data barang',
        icon: 'mdi-account-edit',
      },
      {
        nama: 'Administrator',
        deskripsi: 'Akses tertinggi dan bertanggung jawab dalam pengelolaan web',
        icon: 'mdi-shield-account',
      },
      {
        nama: 'Unit',
        deskripsi: 'Membuat permintaan barang',
        icon: 'mdi-account-tag',
      },
    ],
  },
  petugasUsers: {
    type: Array,
    default: () => [
      {
        id: crypto.randomUUID(),
        name: 'dummy',
        profile_photo_path: '/blank',
        role: 'petugas',
        telepon: '081255437196',
      },
    ],
  },
});
</script>

<template>
  <Head title="Landing Page" />
  <div>
    <!-- Hero Section dengan Moto -->
    <section class="hero-section">
      <div class="hero-background">
        <v-img
          src="/storage/images/depan-igd-rsud-haji-darlan-ismail.jpeg"
          cover
          class="hero-image"
        >
          <div class="hero-overlay"></div>
        </v-img>
      </div>
      <v-container class="hero-content">
        <v-row
          align="center"
          justify="center"
        >
          <v-col
            cols="12"
            class="text-center"
          >
            <v-img
              :src="settings.icon"
              height="300"
              class="mx-auto mb-6"
            />
            <div class="text-shadow mb-4 text-5xl font-bold text-white">
              {{ settings.institutionName }}
            </div>
            <div class="text-shadow text-2xl font-light text-white italic">"{{ settings.moto }}"</div>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Tentang RSUD Section -->
    <section class="bg-white py-16">
      <v-container>
        <v-row>
          <v-col
            cols="12"
            class="mb-8 text-center"
          >
            <h2 class="mb-2 text-4xl font-bold text-blue-900">Tentang RSUD Haji Darlan Ismail</h2>
            <div class="mx-auto h-1 w-20 bg-blue-700"></div>
          </v-col>
        </v-row>
        <v-row justify="center">
          <v-col
            cols="12"
            md="10"
            lg="8"
          >
            <v-card
              elevation="3"
              class="rounded-lg"
            >
              <v-card-text class="pa-8">
                <p class="text-justify text-sm leading-relaxed text-gray-700 md:text-lg">
                  Kegiatan utama RSUD Haji Darlan Ismail adalah usaha pelayanan kesehatan perorangan dengan pendekatan pelayanan medis, tindakan medik
                  dan keperawatan, pelayanan penunjang medik, dan upaya rujukan. Dengan core bisnis adalah pelayanan medis. Dalam upaya menghadapi
                  persaingan global, terutama terhadap kompetitor layanan sejenis di Kabupaten Tanah Laut, RSUD Haji Darlan Ismail berusaha
                  memenangkan persaingan dengan cara menjaga mutu layanan, Leader, SDM dan Sarana Prasarana, serta terjangkau oleh semua lapisan
                  masyarakat. Dengan berbekal sumber daya yang ada, didukung dengan pelayanan PONEK diharapkan mampu mengundang minat masyarakat baik
                  di wilayah Kabupaten Tanah Laut atau di luar wilayah Kabupaten Tanah Laut.
                </p>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Visi Misi Section -->
    <section class="bg-gray-50 py-16">
      <v-container>
        <v-row>
          <v-col
            cols="12"
            class="mb-8 text-center"
          >
            <h2 class="mb-2 text-4xl font-bold text-blue-900">Visi & Misi</h2>
            <div class="mx-auto h-1 w-20 bg-blue-700"></div>
          </v-col>
        </v-row>
        <v-row>
          <v-col
            cols="12"
            md="6"
          >
            <v-card
              class="h-full"
              elevation="2"
            >
              <v-card-title class="bg-blue-700 text-2xl text-white">
                <v-icon class="mr-2">mdi-eye</v-icon>
                Visi
              </v-card-title>
              <v-card-text class="pa-6 text-sm! md:text-lg!">
                {{ settings.visi }}
              </v-card-text>
            </v-card>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-card
              class="h-full"
              elevation="2"
            >
              <v-card-title class="bg-blue-700 text-2xl text-white">
                <v-icon class="mr-2">mdi-target</v-icon>
                Misi
              </v-card-title>
              <v-card-text class="pa-6">
                <ol class="space-y-3 text-sm! md:text-lg!">
                  <li
                    v-for="(item, index) in settings.misi"
                    :key="index"
                    class="flex"
                  >
                    <span class="mr-3 font-bold text-blue-700">{{ index + 1 }}.</span>
                    <span>{{ item }}</span>
                  </li>
                </ol>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Struktur Organisasi Section -->
    <section class="bg-white py-16">
      <v-container>
        <v-row>
          <v-col
            cols="12"
            class="mb-8 text-center"
          >
            <h2 class="mb-2 text-4xl font-bold text-blue-900">Struktur Organisasi</h2>
            <div class="mx-auto h-1 w-20 bg-blue-700"></div>
          </v-col>
        </v-row>
        <v-row justify="center">
          <v-col
            cols="12"
            md="10"
            lg="9"
            class="pa-0"
          >
            <v-card
              elevation="3"
              class="rounded-lg"
            >
              <v-card-text class="pa-0 md:p-4!">
                <v-img
                  src="/storage/images/struktur-organisasi-rsud-haji-darlan-ismail.jpeg"
                  cover
                  class="rounded"
                  alt="Struktur Organisasi RSUD Haji Darlan Ismail"
                />
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Stakeholder Section -->
    <section class="bg-gray-50 py-16">
      <v-container>
        <v-row>
          <v-col
            cols="12"
            class="mb-8 text-center"
          >
            <h2 class="mb-2 text-4xl font-bold text-blue-900">Pengguna Aplikasi</h2>
            <div class="mx-auto mb-4 h-1 w-20 bg-blue-700"></div>
            <p class="text-lg text-gray-600">Stakeholder yang terlibat dalam penggunaan aplikasi</p>
          </v-col>
        </v-row>
        <v-row>
          <v-col
            v-for="(stakeholder, index) in stakeholders"
            :key="index"
            cols="12"
            sm="6"
            md="3"
          >
            <v-card
              class="h-full text-center transition-all! ease-in-out hover:-translate-y-2 hover:shadow-xl hover:delay-100! hover:duration-150!"
              elevation="2"
            >
              <v-card-text class="pa-6">
                <v-avatar
                  size="80"
                  class="mb-4 bg-blue-700"
                >
                  <v-icon size="50">
                    {{ stakeholder.icon }}
                  </v-icon>
                </v-avatar>
                <div class="mb-2 text-xl font-bold text-blue-900">
                  {{ stakeholder.nama }}
                </div>
                <div class="text-base text-gray-600">
                  {{ stakeholder.deskripsi }}
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <section class="bg-white py-16">
      <v-container>
        <v-row>
          <v-col
            cols="12"
            class="mb-8 text-center"
          >
            <h2 class="mb-2 text-4xl font-bold text-blue-900">Kontak Petugas</h2>
            <div class="mx-auto mb-4 h-1 w-20 bg-blue-700"></div>
          </v-col>
        </v-row>
        <v-row class="justify-center!">
          <v-col
            v-for="user of petugasUsers"
            :key="user.id"
            cols="12"
            sm="6"
            md="4"
            lg="3"
          >
            <v-card
              class="h-full text-center transition-all! ease-in-out hover:-translate-y-2 hover:shadow-xl hover:delay-100! hover:duration-150!"
              elevation="2"
            >
              <v-card-text class="pa-6">
                <v-avatar
                  size="120"
                  class="mb-4"
                >
                  <v-img
                    :src="user.profile_photo_path || '/storage/images/default-avatar.png'"
                    alt="Foto Petugas"
                    cover
                  >
                    <template v-slot:placeholder>
                      <v-icon
                        size="80"
                        color="grey-lighten-2"
                        >mdi-account-circle</v-icon
                      >
                    </template>
                  </v-img>
                </v-avatar>

                <div class="mb-2 text-xl font-bold text-blue-900">
                  {{ user.name }}
                </div>

                <v-divider class="my-4"></v-divider>

                <div class="text-left">
                  <div class="mb-3 flex items-center justify-center">
                    <v-btn
                      :href="`tel:${user.telepon}`"
                      color="blue-700"
                      variant="outlined"
                      size="small"
                      icon="mdi-phone"
                    >
                    </v-btn>
                    <a
                      :href="`tel:${user.telepon}`"
                      class="text-gray-700 transition-colors hover:text-blue-700"
                    >
                      {{ user.telepon}}
                    </a>
                  </div>

                  <div class="flex items-center justify-center gap-2 flex-col">
                    <v-btn
                      :href="`https://wa.me/${user?.telepon?.replace(/^0/, '62')}`"
                      target="_blank"
                      color="green"
                      variant="flat"
                      size="small"
                      prepend-icon="mdi-whatsapp"
                    >
                      WhatsApp
                    </v-btn>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>
  </div>
</template>

<style scoped>
.hero-section {
  position: relative;
  min-height: 600px;
  display: flex;
  align-items: center;
  overflow: hidden;
}

.hero-background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
}

.hero-image {
  width: 100%;
  height: 100%;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(13, 71, 161, 0.85) 0%, rgba(21, 101, 192, 0.75) 50%, rgba(25, 118, 210, 0.85) 100%);
  z-index: 1;
}

.hero-content {
  position: relative;
  z-index: 2;
  padding: 5rem 0;
}

.text-shadow {
  text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
}

@media (max-width: 960px) {
  .hero-section {
    min-height: 500px;
  }

  .hero-content {
    padding: 3rem 0;
  }

  .text-5xl {
    font-size: 2.5rem !important;
  }

  .text-2xl {
    font-size: 1.5rem !important;
  }
}

@media (max-width: 600px) {
  .hero-section {
    min-height: 400px;
  }

  .text-5xl {
    font-size: 2rem !important;
  }

  .text-2xl {
    font-size: 1.25rem !important;
  }
}
</style>

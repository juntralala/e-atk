<script setup>
import LandingPageLayout from '@/layouts/LandingPageLayout.vue';
import { ref } from 'vue';

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
      misi: [
        'Misi pertama instansi',
        'Misi kedua instansi',
        'Misi ketiga instansi',
      ],
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
});
</script>

<template>
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
        <v-row align="center" justify="center">
          <v-col cols="12" class="text-center">
            <v-img :src="settings.icon" height="300" class="mx-auto mb-6"/>
            <div class="text-5xl font-bold mb-4 text-white text-shadow">
              {{ settings.institutionName }}
            </div>
            <div class="text-2xl font-light italic text-white text-shadow">
              "{{ settings.moto }}"
            </div>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Tentang RSUD Section -->
    <section class="py-16 bg-white">
      <v-container>
        <v-row>
          <v-col cols="12" class="text-center mb-8">
            <h2 class="text-4xl font-bold text-blue-900 mb-2">
              Tentang RSUD Haji Darlan Ismail
            </h2>
            <div class="w-20 h-1 bg-blue-700 mx-auto"></div>
          </v-col>
        </v-row>
        <v-row justify="center">
          <v-col cols="12" md="10" lg="8">
            <v-card elevation="3" class="rounded-lg">
              <v-card-text class="pa-8">
                <p class="text-lg text-gray-700 leading-relaxed text-justify">
                  Kegiatan utama RSUD Haji Darlan Ismail adalah usaha pelayanan kesehatan perorangan 
                  dengan pendekatan pelayanan medis, tindakan medik dan keperawatan, pelayanan penunjang 
                  medik, dan upaya rujukan. Dengan core bisnis adalah pelayanan medis. Dalam upaya menghadapi 
                  persaingan global, terutama terhadap kompetitor layanan sejenis di Kabupaten Tanah Laut, 
                  RSUD Haji Darlan Ismail berusaha memenangkan persaingan dengan cara menjaga mutu layanan, 
                  Leader, SDM dan Sarana Prasarana, serta terjangkau oleh semua lapisan masyarakat. Dengan 
                  berbekal sumber daya yang ada, didukung dengan pelayanan PONEK diharapkan mampu mengundang 
                  minat masyarakat baik di wilayah Kabupaten Tanah Laut atau di luar wilayah Kabupaten Tanah Laut.
                </p>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Visi Misi Section -->
    <section class="py-16 bg-gray-50">
      <v-container>
        <v-row>
          <v-col cols="12" class="text-center mb-8">
            <h2 class="text-4xl font-bold text-blue-900 mb-2">
              Visi & Misi
            </h2>
            <div class="w-20 h-1 bg-blue-700 mx-auto"></div>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12" md="6">
            <v-card class="h-full" elevation="2">
              <v-card-title class="bg-blue-700 text-white text-2xl">
                <v-icon class="mr-2">mdi-eye</v-icon>
                Visi
              </v-card-title>
              <v-card-text class="pa-6 text-lg!">
                {{ settings.visi }}
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" md="6">
            <v-card class="h-full" elevation="2">
              <v-card-title class="bg-blue-700 text-white text-2xl">
                <v-icon class="mr-2">mdi-target</v-icon>
                Misi
              </v-card-title>
              <v-card-text class="pa-6">
                <ol class="space-y-3 text-lg">
                  <li
                    v-for="(item, index) in settings.misi"
                    :key="index"
                    class="flex"
                  >
                    <span class="font-bold text-blue-700 mr-3">{{ index + 1 }}.</span>
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
    <section class="py-16 bg-white">
      <v-container>
        <v-row>
          <v-col cols="12" class="text-center mb-8">
            <h2 class="text-4xl font-bold text-blue-900 mb-2">
              Struktur Organisasi
            </h2>
            <div class="w-20 h-1 bg-blue-700 mx-auto"></div>
          </v-col>
        </v-row>
        <v-row justify="center">
          <v-col cols="12" md="10" lg="9">
            <v-card elevation="3" class="rounded-lg">
              <v-card-text class="pa-6">
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
    <section class="py-16 bg-gray-50">
      <v-container>
        <v-row>
          <v-col cols="12" class="text-center mb-8">
            <h2 class="text-4xl font-bold text-blue-900 mb-2">
              Pengguna Aplikasi
            </h2>
            <div class="w-20 h-1 bg-blue-700 mx-auto mb-4"></div>
            <p class="text-lg text-gray-600">
              Stakeholder yang terlibat dalam penggunaan aplikasi
            </p>
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
              class="h-full text-center hover:shadow-xl transition-all! hover:duration-150! hover:delay-100! ease-in-out hover:-translate-y-2"
              elevation="2"
            >
              <v-card-text class="pa-6">
                <v-avatar size="80" class="mb-4 bg-blue-700">
                  <v-icon size="50">
                    {{ stakeholder.icon }}
                  </v-icon>
                </v-avatar>
                <div class="text-xl font-bold text-blue-900 mb-2">
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

    <!-- Call to Action -->
    <section class="py-16 bg-blue-700 text-white" v-if="false">
      <v-container>
        <v-row align="center" justify="center">
          <v-col cols="12" md="8" class="text-center">
            <h2 class="text-3xl font-bold mb-4">
              Siap untuk Memulai?
            </h2>
            <p class="text-xl mb-6">
              Bergabunglah dengan sistem kami untuk pengalaman yang lebih baik
            </p>
            <v-btn
              size="x-large"
              color="white"
              variant="flat"
              class="text-blue-700 font-bold"
              to="/login"
            >
              <v-icon class="mr-2">mdi-login</v-icon>
              Login Sekarang
            </v-btn>
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
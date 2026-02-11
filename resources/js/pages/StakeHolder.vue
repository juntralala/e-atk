<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({
  layout: ApplicationLayout,
});

defineProps({
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
  <Head title="Stakeholder Aplikasi"></Head>
  <v-container class="py-8">
    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Stakeholder"
          second-part-title="Aplikasi"
        />
        <p class="text-grey-darken-1 mt-2 text-center animated-subtitle">
          Pengguna utama yang terlibat dalam sistem
        </p>
      </v-col>
    </v-row>
    
    <v-row class="mt-4">
      <v-col
        v-for="(stakeholder, index) in stakeholders"
        :key="index"
        cols="12"
        sm="6"
        md="3"
      >
        <v-card
          class="h-full text-center transition-all duration-300 overflow-hidden stakeholder-card"
          elevation="2"
          hover
          :style="`--animation-delay: ${index * 100}ms`"
        >
          <!-- Decorative top bar with wave animation -->
          <div class="h-2 bg-linear-to-r from-blue-500 to-blue-700 wave-bar"></div>
          
          <!-- Floating particles background -->
          <div class="particles">
            <span class="particle"></span>
            <span class="particle"></span>
            <span class="particle"></span>
            <span class="particle"></span>
            <span class="particle"></span>
          </div>
          
          <v-card-text class="pa-8 relative z-10">
            <!-- Icon with multiple effects -->
            <div class="relative inline-block mb-4 icon-container">
              <!-- Pulsing glow -->
              <div class="absolute inset-0 bg-blue-darken-2 rounded-full blur-xl opacity-30 pulse-glow"></div>
              
              <!-- Rotating ring -->
              <div class="rotating-ring"></div>
              
              <!-- Avatar -->
              <v-avatar 
                size="90" 
                class="bg-blue-darken-2 relative z-10 shadow-lg icon-avatar"
              >
                <v-icon size="50" color="white" class="icon-bounce">
                  {{ stakeholder.icon }}
                </v-icon>
              </v-avatar>
              
              <!-- Orbiting dots -->
              <span class="orbit-dot orbit-dot-1"></span>
              <span class="orbit-dot orbit-dot-2"></span>
              <span class="orbit-dot orbit-dot-3"></span>
            </div>
            
            <!-- Title with gradient animation -->
            <div class="text-xl font-bold mb-3 text-blue-darken-2 animated-title">
              {{ stakeholder.nama }}
            </div>
            
            <!-- Description with fade in -->
            <div class="text-sm text-grey-darken-1 leading-relaxed animated-description">
              {{ stakeholder.deskripsi }}
            </div>
          </v-card-text>
          
          <!-- Multiple shine effects -->
          <div class="card-shine"></div>
          <div class="card-shine-2"></div>
          
          <!-- Border glow on hover -->
          <div class="border-glow"></div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
/* Fade in animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Pulse animation */
@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 0.3;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.5;
  }
}

/* Rotate animation */
@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Wave animation */
@keyframes wave {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

/* Bounce animation */
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}

/* Float animation */
@keyframes float {
  0%, 100% {
    transform: translateY(0) translateX(0);
  }
  33% {
    transform: translateY(-10px) translateX(5px);
  }
  66% {
    transform: translateY(5px) translateX(-5px);
  }
}

/* Particle animation */
@keyframes particleFloat {
  0% {
    transform: translateY(0) translateX(0) scale(0);
    opacity: 0;
  }
  50% {
    opacity: 0.5;
  }
  100% {
    transform: translateY(-100px) translateX(var(--x-movement)) scale(1);
    opacity: 0;
  }
}

/* Orbit animation */
@keyframes orbit {
  from {
    transform: rotate(0deg) translateX(60px) rotate(0deg);
  }
  to {
    transform: rotate(360deg) translateX(60px) rotate(-360deg);
  }
}

/* Text shimmer */
@keyframes shimmer {
  0% {
    background-position: -100% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* Border glow pulse */
@keyframes borderGlow {
  0%, 100% {
    opacity: 0;
    transform: scale(0.95);
  }
  50% {
    opacity: 0.4;
    transform: scale(1.05);
  }
}

/* Card styles */
.stakeholder-card {
  animation: fadeInUp 0.6s ease-out forwards;
  animation-delay: var(--animation-delay);
  opacity: 0;
  position: relative;
  border: 2px solid transparent;
}

.stakeholder-card:hover {
  transform: translateY(-12px) scale(1.02);
  box-shadow: 0 20px 60px rgba(33, 150, 243, 0.3) !important;
}

/* Wave bar */
.wave-bar {
  background-size: 200% 200%;
  animation: wave 3s ease-in-out infinite;
}

/* Icon container */
.icon-container:hover .icon-avatar {
  transform: scale(1.15) rotate(360deg);
}

.icon-avatar {
  transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

/* Pulse glow */
.pulse-glow {
  animation: pulse 2s ease-in-out infinite;
}

/* Rotating ring */
.rotating-ring {
  position: absolute;
  top: -10px;
  left: -10px;
  right: -10px;
  bottom: -10px;
  border: 3px solid rgba(33, 150, 243, 0.3);
  border-radius: 50%;
  border-top-color: rgba(33, 150, 243, 0.8);
  animation: rotate 3s linear infinite;
  opacity: 0;
  transition: opacity 0.3s;
}

.stakeholder-card:hover .rotating-ring {
  opacity: 1;
}

/* Icon bounce */
.icon-bounce {
  animation: bounce 2s ease-in-out infinite;
}

.stakeholder-card:hover .icon-bounce {
  animation: bounce 0.5s ease-in-out infinite;
}

/* Orbiting dots */
.orbit-dot {
  position: absolute;
  width: 8px;
  height: 8px;
  background: linear-gradient(135deg, #2196F3, #1976D2);
  border-radius: 50%;
  top: 50%;
  left: 50%;
  margin: -4px 0 0 -4px;
  opacity: 0;
  box-shadow: 0 0 10px rgba(33, 150, 243, 0.8);
}

.stakeholder-card:hover .orbit-dot {
  opacity: 1;
}

.orbit-dot-1 {
  animation: orbit 3s linear infinite;
}

.orbit-dot-2 {
  animation: orbit 3s linear infinite;
  animation-delay: -1s;
}

.orbit-dot-3 {
  animation: orbit 3s linear infinite;
  animation-delay: -2s;
}

/* Particles */
.particles {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  overflow: hidden;
  pointer-events: none;
}

.particle {
  position: absolute;
  width: 4px;
  height: 4px;
  background: rgba(33, 150, 243, 0.6);
  border-radius: 50%;
  bottom: 0;
  animation: particleFloat 3s ease-in infinite;
}

.particle:nth-child(1) {
  left: 20%;
  animation-delay: 0s;
  --x-movement: 20px;
}

.particle:nth-child(2) {
  left: 40%;
  animation-delay: 0.6s;
  --x-movement: -15px;
}

.particle:nth-child(3) {
  left: 60%;
  animation-delay: 1.2s;
  --x-movement: 25px;
}

.particle:nth-child(4) {
  left: 80%;
  animation-delay: 1.8s;
  --x-movement: -20px;
}

.particle:nth-child(5) {
  left: 50%;
  animation-delay: 2.4s;
  --x-movement: 10px;
}

.stakeholder-card:hover .particle {
  animation-duration: 1.5s;
}

/* Title animation */
.animated-title {
  background: linear-gradient(90deg, #1976D2, #2196F3, #1976D2);
  background-size: 200% 100%;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: shimmer 3s linear infinite;
}

.stakeholder-card:hover .animated-title {
  animation-duration: 1s;
}

/* Description animation */
.animated-description {
  animation: fadeInUp 0.8s ease-out forwards;
  animation-delay: calc(var(--animation-delay) + 200ms);
  opacity: 0;
}

/* Subtitle animation */
.animated-subtitle {
  animation: fadeInUp 0.6s ease-out forwards;
  animation-delay: 0.3s;
  opacity: 0;
}

/* Shine effects */
.card-shine {
  position: absolute;
  top: 0;
  left: -100%;
  width: 50%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.4),
    transparent
  );
  transition: left 0.6s ease;
  pointer-events: none;
  z-index: 1;
}

.stakeholder-card:hover .card-shine {
  left: 150%;
}

.card-shine-2 {
  position: absolute;
  top: 0;
  left: -100%;
  width: 30%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(33, 150, 243, 0.3),
    transparent
  );
  transition: left 0.8s ease;
  pointer-events: none;
  z-index: 1;
  transition-delay: 0.2s;
}

.stakeholder-card:hover .card-shine-2 {
  left: 150%;
}

/* Border glow */
.border-glow {
  display: none;
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: linear-gradient(45deg, #2196F3, #1976D2, #2196F3);
  border-radius: inherit;
  z-index: -1;
  opacity: 0;
  animation: borderGlow 2s ease-in-out infinite;
  filter: blur(10px);
}

.stakeholder-card:hover .border-glow {
  opacity: 1;
}
</style>
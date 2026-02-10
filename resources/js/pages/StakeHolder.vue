<template>
  <v-container fluid class="pa-6">
    <!-- Page Header -->
    <v-row class="mb-6">
      <v-col cols="12" md="8">
        <h1 class="text-h3 font-weight-bold text-blue-900 mb-2">
          Pemangku Kepentingan
        </h1>
        <p class="text-h6 text-grey-darken-1">
          Kelola akses dan peran pengguna sistem gudang
        </p>
      </v-col>
      <v-col cols="12" md="4" class="d-flex align-center justify-end">
        <v-btn
          color="blue-darken-2"
          size="large"
          elevation="2"
          @click="openAddModal"
        >
          <v-icon start>mdi-plus</v-icon>
          Tambah Pengguna
        </v-btn>
      </v-col>
    </v-row>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col
        v-for="(stat, index) in stats"
        :key="stat.role"
        cols="12"
        sm="6"
        md="3"
      >
        <v-card
          :color="stat.color"
          elevation="3"
          class="stat-card"
          :style="{ '--delay': index * 0.1 + 's' }"
        >
          <v-card-text class="pa-5">
            <v-row align="center" no-gutters>
              <v-col cols="auto" class="mr-4">
                <v-avatar
                  :color="stat.lightColor"
                  size="60"
                  class="elevation-4"
                >
                  <v-icon :color="stat.color" size="32">
                    {{ stat.icon }}
                  </v-icon>
                </v-avatar>
              </v-col>
              <v-col>
                <div class="text-h3 font-weight-bold text-white mb-1">
                  {{ stat.count }}
                </div>
                <div class="text-subtitle-1 font-weight-medium text-white">
                  {{ stat.role }}
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters Section -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <v-text-field
          v-model="searchQuery"
          density="comfortable"
          placeholder="Cari nama atau email..."
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          color="blue-darken-2"
          hide-details
          clearable
        />
      </v-col>
      <v-col cols="12" md="6">
        <v-chip-group
          v-model="activeFilter"
          selected-class="text-white"
          color="blue-darken-2"
          mandatory
        >
          <v-chip
            v-for="filter in roleFilters"
            :key="filter.value"
            :value="filter.value"
            variant="outlined"
            color="blue-darken-2"
            size="large"
          >
            {{ filter.label }}
          </v-chip>
        </v-chip-group>
      </v-col>
    </v-row>

    <!-- Data Table -->
    <v-card elevation="2" class="stakeholders-table">
      <v-data-table
        :headers="headers"
        :items="filteredStakeholders"
        :search="searchQuery"
        :items-per-page="10"
        class="elevation-0"
      >
        <!-- Name Column -->
        <template v-slot:item.name="{ item }">
          <div class="d-flex align-center py-3">
            <v-avatar
              :color="item.avatarColor"
              size="44"
              class="mr-3"
            >
              <span class="text-white font-weight-bold">
                {{ item.initials }}
              </span>
            </v-avatar>
            <div>
              <div class="font-weight-bold text-body-1">
                {{ item.name }}
              </div>
              <div class="text-grey-darken-1 text-body-2">
                {{ item.email }}
              </div>
            </div>
          </div>
        </template>

        <!-- Role Column -->
        <template v-slot:item.role="{ item }">
          <v-chip
            :color="getRoleColor(item.role)"
            variant="flat"
            size="small"
            class="font-weight-medium"
          >
            {{ getRoleLabel(item.role) }}
          </v-chip>
        </template>

        <!-- Unit Column -->
        <template v-slot:item.unit="{ item }">
          <span class="text-body-2">{{ item.unit || '-' }}</span>
        </template>

        <!-- Status Column -->
        <template v-slot:item.isActive="{ item }">
          <v-chip
            :color="item.isActive ? 'success' : 'error'"
            variant="flat"
            size="small"
            class="font-weight-medium"
          >
            <v-icon start size="x-small">
              mdi-circle
            </v-icon>
            {{ item.isActive ? 'Aktif' : 'Nonaktif' }}
          </v-chip>
        </template>

        <!-- Joined Date Column -->
        <template v-slot:item.joinedDate="{ item }">
          <span class="text-body-2 text-grey-darken-1">
            {{ item.joinedDate }}
          </span>
        </template>

        <!-- Actions Column -->
        <template v-slot:item.actions="{ item }">
          <div class="d-flex ga-2">
            <v-btn
              icon="mdi-pencil"
              size="small"
              variant="text"
              color="blue-darken-2"
              @click="editStakeholder(item)"
            />
            <v-btn
              icon="mdi-delete"
              size="small"
              variant="text"
              color="red-darken-2"
              @click="deleteStakeholder(item)"
            />
          </div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, computed } from 'vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';

defineOptions({
  layout: ApplicationLayout,
});

const searchQuery = ref('');
const activeFilter = ref('all');

const headers = [
  { title: 'Nama & Email', key: 'name', sortable: true },
  { title: 'Peran', key: 'role', sortable: true },
  { title: 'Unit', key: 'unit', sortable: true },
  { title: 'Status', key: 'isActive', sortable: true },
  { title: 'Bergabung', key: 'joinedDate', sortable: true },
  { title: 'Aksi', key: 'actions', sortable: false, align: 'center' },
];

const roleFilters = [
  { label: 'Semua', value: 'all' },
  { label: 'Administrator', value: 'admin' },
  { label: 'Bendahara', value: 'treasurer' },
  { label: 'Petugas', value: 'officer' },
  { label: 'Unit', value: 'unit' },
];

const stakeholders = ref([
  {
    id: 1,
    name: 'Ahmad Suryadi',
    email: 'ahmad.suryadi@gudang.co.id',
    role: 'admin',
    unit: null,
    isActive: true,
    joinedDate: '12 Jan 2024',
    initials: 'AS',
    avatarColor: '#1565C0'
  },
  {
    id: 2,
    name: 'Siti Nurhaliza',
    email: 'siti.nurhaliza@gudang.co.id',
    role: 'treasurer',
    unit: 'Keuangan',
    isActive: true,
    joinedDate: '15 Jan 2024',
    initials: 'SN',
    avatarColor: '#1976D2'
  },
  {
    id: 3,
    name: 'Budi Santoso',
    email: 'budi.santoso@gudang.co.id',
    role: 'officer',
    unit: 'Gudang A',
    isActive: true,
    joinedDate: '20 Jan 2024',
    initials: 'BS',
    avatarColor: '#1E88E5'
  },
  {
    id: 4,
    name: 'Dewi Lestari',
    email: 'dewi.lestari@gudang.co.id',
    role: 'officer',
    unit: 'Gudang B',
    isActive: true,
    joinedDate: '22 Jan 2024',
    initials: 'DL',
    avatarColor: '#2196F3'
  },
  {
    id: 5,
    name: 'Eko Prasetyo',
    email: 'eko.prasetyo@gudang.co.id',
    role: 'unit',
    unit: 'Unit Produksi',
    isActive: true,
    joinedDate: '25 Jan 2024',
    initials: 'EP',
    avatarColor: '#42A5F5'
  },
  {
    id: 6,
    name: 'Rina Wijaya',
    email: 'rina.wijaya@gudang.co.id',
    role: 'unit',
    unit: 'Unit Distribusi',
    isActive: false,
    joinedDate: '28 Jan 2024',
    initials: 'RW',
    avatarColor: '#64B5F6'
  },
]);

const stats = computed(() => [
  {
    role: 'Administrator',
    count: stakeholders.value.filter(s => s.role === 'admin').length,
    color: 'blue-darken-4',
    lightColor: 'blue-lighten-4',
    icon: 'mdi-shield-account'
  },
  {
    role: 'Bendahara',
    count: stakeholders.value.filter(s => s.role === 'treasurer').length,
    color: 'blue-darken-3',
    lightColor: 'blue-lighten-3',
    icon: 'mdi-account-tie'
  },
  {
    role: 'Petugas',
    count: stakeholders.value.filter(s => s.role === 'officer').length,
    color: 'blue-darken-2',
    lightColor: 'blue-lighten-2',
    icon: 'mdi-account-edit'
  },
  {
    role: 'Unit',
    count: stakeholders.value.filter(s => s.role === 'unit').length,
    color: 'blue-darken-1',
    lightColor: 'blue-lighten-1',
    icon: 'mdi-account-tag'
  },
]);

const filteredStakeholders = computed(() => {
  let filtered = stakeholders.value;
  
  if (activeFilter.value !== 'all') {
    filtered = filtered.filter(s => s.role === activeFilter.value);
  }
  
  return filtered;
});

function getRoleLabel(role) {
  const labels = {
    admin: 'Administrator',
    treasurer: 'Bendahara',
    officer: 'Petugas',
    unit: 'Unit'
  };
  return labels[role] || role;
}

function getRoleColor(role) {
  const colors = {
    admin: 'blue-darken-4',
    treasurer: 'blue-darken-3',
    officer: 'blue-darken-2',
    unit: 'blue-darken-1'
  };
  return colors[role] || 'blue';
}

function openAddModal() {
  console.log('Open add modal');
}

function editStakeholder(stakeholder) {
  console.log('Edit:', stakeholder);
}

function deleteStakeholder(stakeholder) {
  console.log('Delete:', stakeholder);
}
</script>

<style scoped>
.stat-card {
  transition: all 0.3s ease;
  animation: fadeInUp 0.6s ease backwards;
  animation-delay: var(--delay);
}

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

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(21, 101, 192, 0.2) !important;
}

.stakeholders-table {
  animation: fadeIn 0.8s ease 0.4s backwards;
}

@keyframes fadeIn {
  from { 
    opacity: 0; 
  }
  to { 
    opacity: 1; 
  }
}

/* Custom scrollbar untuk tabel */
:deep(.v-data-table) {
  background: transparent;
}

:deep(.v-data-table__wrapper) {
  scrollbar-width: thin;
  scrollbar-color: #1976D2 #E3F2FD;
}

:deep(.v-data-table__wrapper)::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

:deep(.v-data-table__wrapper)::-webkit-scrollbar-track {
  background: #E3F2FD;
  border-radius: 4px;
}

:deep(.v-data-table__wrapper)::-webkit-scrollbar-thumb {
  background: #1976D2;
  border-radius: 4px;
}

:deep(.v-data-table__wrapper)::-webkit-scrollbar-thumb:hover {
  background: #1565C0;
}

/* Table header styling */
:deep(.v-data-table-header) {
  background-color: #E3F2FD !important;
}

:deep(.v-data-table-header th) {
  font-weight: 700 !important;
  color: #0D47A1 !important;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.8rem !important;
}

/* Table row hover effect */
:deep(.v-data-table__tr):hover {
  background-color: #F5F5F5 !important;
}

/* Chip animations */
.v-chip {
  transition: all 0.2s ease;
}

.v-chip:hover {
  transform: scale(1.05);
}

/* Button hover effects */
.v-btn {
  transition: all 0.3s ease;
}

.v-btn:hover {
  transform: translateY(-2px);
}

/* Text field focus animation */
:deep(.v-field--focused) {
  box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

/* Avatar pulse animation untuk status aktif */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { formatDateTimeIndonesia } from '@/lib/formatters';

const notifications = ref({ data: [], currentPage: 1, perPage: 8, lastPage: 1, total: 0 });
const currentPage = ref(1);
const unreadCount = ref(0);
const isInitialLoading = ref(false);
const isLoadingMore = ref(false);
const isReloading = ref(false);

onMounted(() => {
  loadUnreadCount();
});

async function loadNotifications(page = 1) {
  try {
    // Set loading hanya untuk load more
    if (page > 1) {
      isLoadingMore.value = true;
    }
    
    const response = await axios.get(route('notifications'), { params: { page } });
    
    if (page === 1) {
      notifications.value = response.data;
    } else {
      notifications.value.data.push(...response.data.data);
      notifications.value.currentPage = response.data.currentPage;
    }
    currentPage.value = page;
  } catch (err) {
    console.error(err);
  } finally {
    isLoadingMore.value = false;
  }
}

async function loadUnreadCount() {
  try {
    const response = await axios.get(route('notifications.unread.count'));
    unreadCount.value = response.data.data.unread_count;
  } catch (err) {
    console.error(err);
  }
}

async function reloadNotifications() {
  try {
    isReloading.value = true;
    
    // Jika notifikasi masih kosong, tampilkan initial loading
    if (notifications.value.data.length === 0) {
      isInitialLoading.value = true;
    }
    
    const response = await axios.get(route('notifications'));
    notifications.value = response?.data;
    await loadUnreadCount();
  } catch (err) {
    console.error(err);
  } finally {
    isInitialLoading.value = false;
    isReloading.value = false;
  }
}

function markRead(notificationId) {
  axios.post(route('notifications.read', notificationId))
    .then(() => {
      loadUnreadCount();
    })
    .catch(err => console.error(err));
}

function loadMore() {
  loadNotifications(currentPage.value + 1);
}

// Format waktu relatif untuk notifikasi
function formatRelativeTime(dateString) {
  const now = new Date();
  const date = new Date(dateString);
  const diffInMs = now - date;
  const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
  const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
  
  // Jika kurang dari 24 jam, tampilkan waktu relatif
  if (diffInHours < 24) {
    if (diffInMinutes < 1) {
      return 'Baru saja';
    } else if (diffInMinutes < 60) {
      return `${diffInMinutes} menit yang lalu`;
    } else {
      return `${diffInHours} jam yang lalu`;
    }
  }
  
  // Jika lebih dari 24 jam, tampilkan tanggal lengkap
  return formatDateTimeIndonesia(dateString);
}

let interval = null;
onMounted(() => interval = setInterval(loadUnreadCount, 5_000));
onUnmounted(() => clearInterval(interval));
</script>

<template>
  <v-btn 
    id="btn-notification" 
    rounded
    class="me-1 pa-0"
    width="50" 
    height="50" 
    @click="reloadNotifications"
  >
    <v-badge v-if="unreadCount > 0" color="warning" dot>
      <v-icon icon="mdi-bell" size="26" />
    </v-badge>
    <v-icon v-else icon="mdi-bell" size="26" />
  </v-btn>

  <v-menu activator="#btn-notification" width="400" :close-on-content-click="false">
    <!-- Content - ketika loading dan notifikasi masih kosong -->
    <v-list v-if="isInitialLoading">
      <v-list-item class="d-flex justify-center align-center" style="min-height: 200px;">
        <v-progress-circular
          indeterminate
          color="primary"
          size="50"
        />
      </v-list-item>
    </v-list>

    <!-- Content - Ketika ada notifikasi -->
    <v-list v-else-if="notifications.total > 0">
      <v-list-item-title class="font-semibold! text-xl! ms-5">
        Notifikasi
      </v-list-item-title>

      <v-list-item 
        v-for="notification in notifications.data" 
        :key="notification.id"
        @click="markRead(notification.id)" 
        :class="notification.read_at ? 'bg-grey-50!' : 'bg-blue-50!'" 
        density="compact"
      >
        <template #prepend>
          <v-avatar :icon="notification.icon || 'mdi-alert'" />
        </template>
        <span class="ms-2">
          <div :class="notification.read_at ? 'text-grey-700' : 'font-semibold'">
            {{ notification.message }}
          </div>
          <span class="text-xs text-grey-600">
            {{ formatRelativeTime(notification.created_at) }}
          </span>
        </span>
      </v-list-item>

      <v-divider v-if="currentPage < notifications.lastPage" class="my-2" />
      
      <!-- Tombol Load More dengan loading -->
      <v-list-item 
        v-if="currentPage < notifications.lastPage" 
        @click="loadMore" 
        density="compact" 
        class="pa-0 ma-0"
      >
        <v-btn 
          block 
          variant="text" 
          size="small"
          :loading="isLoadingMore"
        >
          Lebih Banyak
        </v-btn>
      </v-list-item>
    </v-list>

    <!-- Content - ketika tidak ada notifikasi -->
    <v-list v-else>
      <v-list-item>
        Tidak ada notifikasi
      </v-list-item>
    </v-list>
  </v-menu>
</template>
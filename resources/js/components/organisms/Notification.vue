<script setup>
import { formatDateTimeIndonesia } from '@/lib/formatters';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';

const notifications = ref({ data: [], currentPage: 1, perPage: 8, lastPage: 1, total: 0 });
const currentPage = ref(1);
const unreadCount = ref(false);
const isInitialLoading = ref(false);
const isLoadingMore = ref(false);
const isReloading = ref(false);

onMounted(() => {
  isUnreadNotificationExists();
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

async function isUnreadNotificationExists() {
  //
  try {
    const response = await axios.get(route('notifications.unread.exists'));
    unreadCount.value = response.data.data; // <- boolean
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
    await isUnreadNotificationExists();
  } catch (err) {
    console.error(err);
  } finally {
    isInitialLoading.value = false;
    isReloading.value = false;
  }
}

function markRead(notificationId) {
  axios
    .post(route('notifications.read', notificationId))
    .then(() => {
      isUnreadNotificationExists();
    })
    .catch((err) => console.error(err));
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
onMounted(() => (interval = setInterval(isUnreadNotificationExists, 2_000)));
onUnmounted(() => clearInterval(interval));
</script>

<template>
  <v-btn
    id="btn-notification"
    rounded
    class="pa-0 me-1"
    width="50"
    height="50"
    @click="reloadNotifications"
  >
    <v-badge
      v-if="unreadCount"
      color="warning"
      dot
    >
      <v-icon
        icon="mdi-bell"
        size="26"
      />
    </v-badge>
    <v-icon
      v-else
      icon="mdi-bell"
      size="26"
    />
  </v-btn>

  <v-menu
    activator="#btn-notification"
    width="400"
    :close-on-content-click="false"
  >
    <!-- Content - ketika loading dan notifikasi masih kosong START-->
    <v-list v-if="isInitialLoading">
      <v-list-item
        class="d-flex align-center justify-center"
        style="min-height: 200px"
      >
        <v-progress-circular
          indeterminate
          color="primary"
          size="50"
        />
      </v-list-item>
    </v-list>
    <!-- Content - ketika loading dan notifikasi masih kosong END-->

    <!-- Content - Ketika ada notifikasi START -->
    <v-list v-else-if="notifications.total > 0">
      <v-list-item-title class="ms-5 text-xl! font-semibold!"> Notifikasi </v-list-item-title>

      <Link
        v-for="notification in notifications.data"
        preserve-scroll
        preserve-state
        :key="notification.id"
        :href="notification.url"
        @click="notification.read_at = new Date().toISOString()"
      >
        <v-list-item
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
            <span class="text-grey-600 text-xs">
              {{ formatRelativeTime(notification.created_at) }}
            </span>
          </span>
        </v-list-item>
      </Link>

      <v-divider
        v-if="currentPage < notifications.lastPage"
        class="my-2"
      />

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
    <!-- Content - Ketika ada notifikasi END -->

    <!-- Content - ketika tidak ada notifikasi START -->
    <v-list v-else>
      <v-list-item> Tidak ada notifikasi </v-list-item>
    </v-list>
    <!-- Content - ketika tidak ada notifikasi END -->
  </v-menu>
</template>

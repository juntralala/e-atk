<script setup>
import ProfilePhoto from '@/components/atoms/ProfilePhoto.vue';
import DrawerItem from '@/components/molecules/DrawerItem.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import Footer from '@/components/organisms/Footer.vue';
import Notification from '@/components/organisms/Notification.vue';
import {
  canAccessItemAdditionReport,
  canAccessItemExpenditureReport,
  canAccessItemReport,
  canAccessItemRequestReport,
  canAccessUnitExpenditureReport,
  canAddItem,
  canInItemListPage,
  canInItemRequestPage,
  canInUnitPage,
  canInUserPage,
  canManageItem,
  canReadReport,
  canRequestItem,
  canSeeMasterData,
  canSetting,
} from '@/lib/can';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUpdated, ref, watch } from 'vue';
import { useDisplay } from 'vuetify/lib/composables/display.mjs';

const { auth, settings, errors } = defineProps({
  auth: {
    type: Object,
    default: null,
  },
  settings: {
    type: Object,
    default: null,
  },
  errors: {
    type: Object,
    default: null,
  },
});

// penangkap error global START
const showAlert = ref(false);
const alertMessage = ref('');
watch(
  () => errors?.message,
  (newVal) => {
    if (!!newVal) {
      showAlert.value = true;
      alertMessage.value = errors?.message;
    }
  },
  { immediate: true },
);
watch(
  () => showAlert.value,
  (newVal) => {
    if (!newVal) {
      errors.message = '';
    }
  },
);
// penangkap error global END

const { user } = auth;
const { mdAndUp } = useDisplay();

const showDrawer = ref(mdAndUp);
const selectedMenu = ref([]);
const expandedGroups = ref([]);

function toggleDrawer() {
  showDrawer.value = !showDrawer.value;
}

// ambil route saat ini dan isi nilai dari selectedMenu.value
// agar menampilkan hightlight pada saat halman pertama kali dibuka
function hightlightSelectedMenu() {
  selectedMenu.value = [route(route().current())];
}

onMounted(async function () {
  // expand group menu yang terpilih
  switch (route().current()) {
    case 'users':
    case 'items':
    case 'units':
      expandedGroups.value = ['master'];
      break;
    case 'items.exports.view':
    case 'items.additions.exports.view':
    case 'items.requests.exports.view':
    case 'items.expenditures.exports.view':
    case 'expenditures.units.exports.view':
      expandedGroups.value = ['report'];
      break;
  }

  hightlightSelectedMenu();
});
onUpdated(function () {
  hightlightSelectedMenu();
});
</script>

<template>
  <Head v-slot="props">
    <link
      rel="shortcut icon"
      :href="settings?.icon || 'favicon.ico'"
      type="image/x-icon"
    />
    <title>{{ settings?.applicationName }}</title>
  </Head>
  <v-app>
    <v-app-bar
      elevation="1"
      color="blue-darken-2"
      class="pe-2"
    >
      <Link href="/">
        <v-app-bar-title>
          <v-icon
            icon="mdi-menu"
            @click="toggleDrawer"
          />
          <span class="ms-2">
            <v-avatar variant="text">
              <v-img :src="settings?.icon" />
            </v-avatar>
            <span class="ms-1">{{ settings?.applicationName }}</span>
          </span>
        </v-app-bar-title>
      </Link>
      <template #append>
        <Notification />
        <ProfilePhoto
          :url="auth?.user?.profile_photo_path"
          id="profile-avatar"
        />
        <v-menu
          activator="#profile-avatar"
          :close-on-content-click="false"
        >
          <v-card min-width="170">
            <v-card-title>{{ user?.name }}</v-card-title>
            <v-card-subtitle>{{ user?.role?.name }}</v-card-subtitle>
            <v-divider />
            <v-list density="comfortable">
              <Link :href="route('profile')">
                <v-list-item value="profile">Profil</v-list-item>
              </Link>
              <Link
                :href="route('logout')"
                class="w-full! text-left"
                method="post"
              >
                <v-list-item value="logout"> Log out </v-list-item>
              </Link>
            </v-list>
          </v-card>
        </v-menu>
      </template>
    </v-app-bar>
    <v-navigation-drawer v-model="showDrawer">
      <v-list
        v-model:selected="selectedMenu"
        v-model:opened="expandedGroups"
        color="blue"
        mandatory
      >
        <DrawerItem
          v-if="canInItemListPage(user)"
          :href="route('items', { mode: 'view' })"
          icon="mdi-package"
          >Daftar Barang</DrawerItem
        >
        <DrawerItem
          v-if="canAddItem(user)"
          :href="route('items.additions')"
          icon="mdi-package-variant-plus"
          >Penambahan Barang</DrawerItem
        >
        <DrawerItem
          v-if="canRequestItem(user)"
          :href="route('items.requests.form')"
          icon="mdi-clipboard-list"
          >Minta Barang</DrawerItem
        >
        <DrawerItem
          v-if="canInItemRequestPage(user)"
          :href="route('items.requests')"
          icon="mdi-clipboard-text-clock"
          >Permintaan</DrawerItem
        >
        <v-list-group
          v-if="canReadReport(user)"
          value="report"
        >
          <template #activator="{ props }">
            <v-list-item :="props">
              <v-list-item-title>
                <div class="flex items-baseline gap-1"><v-icon icon="mdi-file-chart" />Laporan</div>
              </v-list-item-title>
            </v-list-item>
          </template>
          <DrawerItem
            v-if="canAccessItemReport(user)"
            :href="route('items.exports.view')"
            icon="mdi-package-variant"
            >Barang</DrawerItem
          >
          <DrawerItem
            v-if="canAccessItemAdditionReport(user)"
            :href="route('items.additions.exports.view')"
            icon="mdi-package-up"
            >Penambahan Barang</DrawerItem
          >
          <DrawerItem
            v-if="canAccessItemRequestReport(user)"
            :href="route('items.requests.exports.view')"
            icon="mdi-file-document-edit"
            >Permintaan Barang</DrawerItem
          >
          <DrawerItem
            v-if="canAccessItemExpenditureReport(user)"
            :href="route('items.expenditures.exports.view')"
            icon="mdi-receipt-text"
            >Pengeluaran Barang</DrawerItem
          >
          <DrawerItem
            v-if="canAccessUnitExpenditureReport(user)"
            :href="route('expenditures.units.exports.view')"
            icon="mdi-wallet-outline"
            >Pengeluaran Unit</DrawerItem
          >
        </v-list-group>
        <v-list-group
          v-if="canSeeMasterData(user)"
          value="master"
        >
          <template #activator="{ props }">
            <v-list-item :="props">
              <v-list-item-title>
                <div class="flex items-baseline gap-1"><v-icon icon="mdi-database" />Master</div>
              </v-list-item-title>
            </v-list-item>
          </template>
          <DrawerItem
            v-if="canInUnitPage(user)"
            :href="route('units')"
            icon="mdi-ruler"
            >Satuan</DrawerItem
          >
          <DrawerItem
            v-if="canManageItem(user)"
            :href="route('items')"
            icon="mdi-package-variant-closed"
            >Barang</DrawerItem
          >
          <DrawerItem
            v-if="canInUserPage(user)"
            :href="route('users')"
            icon="mdi-account-group"
            >Akun</DrawerItem
          >
        </v-list-group>
        <DrawerItem
          v-if="canSetting(user)"
          :href="route('settings')"
          icon="mdi-cog"
          >Pengaturan</DrawerItem
        >
        <DrawerItem
          :href="route('stakeholders')"
          icon="mdi-account-tie"
          >Pemangku Kepentingan</DrawerItem
        >
      </v-list>
    </v-navigation-drawer>
    <v-main>
      <slot />
    </v-main>
    <AlertDialog
      v-model="showAlert"
      :message="alertMessage"
      title="Error"
    />

    <Footer
      :namaInstansi="settings?.institutionName"
      :alamatInstansi="settings?.institutionAddress"
    ></Footer>
  </v-app>
</template>

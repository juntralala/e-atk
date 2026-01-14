<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted, onUpdated, ref } from 'vue';
import Notification from '@/components/organisms/Notification.vue';

const page = usePage();
const user = page.props?.auth?.user;

const showDrawer = ref(false);
const selectedMenu = ref([]);
const expandedGroups = ref([]);

function showDrawerOnMdScreenSize() {
    if (window.innerWidth > 1024) {
        showDrawer.value = true;
    }
}
function toggleDrawer() {
    showDrawer.value = !showDrawer.value;
}
function updateSelectedMenu(menu) {
    selectedMenu.value = [menu];
}
function selectMenu() {
    switch (route().current()) {
        case 'home': updateSelectedMenu('home'); break;
        case 'items.inbound': updateSelectedMenu('items.inbound'); break;
        case 'items.outbound': updateSelectedMenu('items.outbound'); break;
        case 'items.transactions.history': updateSelectedMenu('items.transactions.history'); break;
        case 'settings': updateSelectedMenu('settings'); break;
        case 'users': updateSelectedMenu('users'); break;
        case 'items': updateSelectedMenu('items'); break;
        case 'items.units': updateSelectedMenu('items.units'); break;
        case 'items.stocks': updateSelectedMenu('items.stocks'); break;
        case 'items.skus': updateSelectedMenu('items.skus'); break;
        case 'recipients': updateSelectedMenu('recipients'); break;
        default: updateSelectedMenu(null);
    }
}

onMounted(async function () {
    showDrawerOnMdScreenSize();

    // expand group menu yang terpilih
    switch (route().current()) {
        case 'users': expandedGroups.value = ['master']; break;
        case 'items': expandedGroups.value = ['master']; break;
        // case 'supliers': expandedGroups.value = ['master']; break;
        case 'items.units': expandedGroups.value = ['master']; break;
        case 'recipients': expandedGroups.value = ['master']; break;
    }

    selectMenu();
});
onUpdated(function () {
    selectMenu();
});

</script>

<template>

    <Head v-slot="props">
        <link rel="shortcut icon" :href="$page?.props?.settings?.app_icon || 'favicon.ico'" type="image/x-icon">
            <title>{{ $page?.props?.settings?.app_name }}</title>
    </Head>
    <v-app>
        <v-app-bar elevation="1" color="blue-darken-2" class="pe-2">
            <v-app-bar-title>
                <v-icon icon="mdi-menu" @click="toggleDrawer"></v-icon>
                <span class="ms-2">{{ $page.props.settings.app_name }}</span>
            </v-app-bar-title>
            <template #append>
                <Notification/>
                <v-avatar id="profile-avatar">
                    <img v-if="page.props.auth?.user?.profile_photo_path"
                        :src="page.props.auth?.user?.profile_photo_path" alt="alt">
                    <v-icon v-else class="cursor-pointer" icon="mdi-account" size="x-large" />
                </v-avatar>
                <v-menu activator="#profile-avatar" :close-on-content-click="false">
                    <v-card>
                        <v-card-title>{{ $page.props.auth.user?.name }}</v-card-title>
                        <v-card-subtitle>{{ $page.props.auth.user?.role?.name }}</v-card-subtitle>
                        <v-divider />
                        <v-list density="comfortable">
                            <Link :href="route('account.profile')">
                                <v-list-item value="profile">Profil</v-list-item>
                            </Link>
                            <Link :href="route('logout')" class="w-full! text-left" method="post">
                                <v-list-item value="logout">
                                    Log out
                                </v-list-item>
                            </Link>
                        </v-list>
                    </v-card>
                </v-menu>
            </template>
        </v-app-bar>
        <v-navigation-drawer v-model="showDrawer">
            <v-list v-model:selected="selectedMenu" v-model:opened="expandedGroups" color="blue" mandatory>
                <Link :href="route('home')">
                    <v-list-item value="home">
                        <div class="flex items-baseline gap-1">
                            <v-icon icon="mdi-home" />
                            <div>Dashboard</div>
                        </div>
                    </v-list-item>
                </Link>
                <Link :href="route('items.inbound')">
                    <v-list-item value="items.inbound">
                        <div class="flex items-baseline gap-1">
                            <v-icon icon="mdi-card-plus" />
                            <div>Barang Masuk</div>
                        </div>
                    </v-list-item>
                </Link>
                <Link :href="route('items.outbound')">
                    <v-list-item value="items.outbound">
                        <div class="flex items-baseline gap-1">
                            <v-icon icon="mdi-card-minus" />
                            <div>Barang Keluar</div>
                        </div>
                    </v-list-item>
                </Link>
                <Link v-if="false" :href="route('items.stocks')">
                    <v-list-item value="items.stocks">
                        <div class="flex items-baseline gap-1"><v-icon icon="mdi-cube-outline" /><span>Stok</span>
                        </div>
                    </v-list-item>
                </Link>
                <Link :href="route('items.transactions.history')">
                    <v-list-item value="items.transactions.history">
                        <div class="flex items-baseline gap-1">
                            <v-icon icon="mdi-history" />
                            <div>Riwayat</div>
                        </div>
                    </v-list-item>
                </Link>
                <Link :href="route('items.skus')">
                    <v-list-item value="items.skus">
                        <div class="flex items-baseline gap-1">
                            <v-icon icon="mdi-cube" />
                            <div>SKU</div>
                        </div>
                    </v-list-item>
                </Link>
                <v-list-group value="master">
                    <template #activator="{ props }">
                        <v-list-item :="props">
                            <v-list-item-title>
                                <div class="flex items-baseline gap-1">
                                    <v-icon icon="mdi-shape" />Master
                                </div>
                            </v-list-item-title>
                        </v-list-item>
                    </template>
                    <!-- <v-list-item value="1">
                        <div class="flex items-baseline gap-1"><v-icon icon="mdi-tag" /><span>Jenis Barang</span></div>
                    </v-list-item>
                    <v-list-item value="2">
                        <div class="flex items-baseline gap-1"><v-icon icon="mdi-face-agent" /><span>Suplier</span>
                        </div>
                    </v-list-item> -->
                    <Link :href="route('items.units')">
                        <v-list-item value="items.units">
                            <div class="flex items-baseline gap-1"><v-icon icon="mdi-scale" /><span>Unit Ukuran</span>
                            </div>
                        </v-list-item>
                    </Link>
                    <Link :href="route('recipients')">
                        <v-list-item value="recipients">
                            <div class="flex items-baseline gap-1"><v-icon icon="mdi-account" /><span>Penerima</span>
                            </div>
                        </v-list-item>
                    </Link>
                    <Link :href="route('items')">
                        <v-list-item value="items">
                            <div class="flex items-baseline gap-1"><v-icon icon="mdi-cube-outline" /><span>Barang</span>
                            </div>
                        </v-list-item>
                    </Link>
                    <Link v-if="user?.role?.name == 'admin'" :href="route('users')">
                        <v-list-item value="users">
                            <div class="flex items-baseline gap-1"><v-icon icon="mdi-account-group" /><span>Akun</span>
                            </div>
                        </v-list-item>
                    </Link>
                </v-list-group>
                <Link v-if="user.role.name == 'admin'" :href="route('settings')">
                    <v-list-item value="settings">
                        <v-icon icon="mdi-cog"></v-icon> Pengaturan
                    </v-list-item>
                </Link>
            </v-list>
        </v-navigation-drawer>
        <v-main>
            <slot />
        </v-main>

        <v-footer app color="secondary" class="static! max-h-12 self-end">
            <p class="w-full! text-center">
                ©2026 {{ $page?.props?.settings?.company_name }} -
                {{ $page?.props?.settings?.company_address }}
            </p>
        </v-footer>
    </v-app>
</template>
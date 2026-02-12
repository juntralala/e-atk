<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import CreateUpdateUserForm from '@/components/organisms/CreateUpdateUserForm.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const { auth, users: usersProp } = defineProps({
  auth: {
    type: Object,
    default: null,
  },
  users: {
    type: Object,
    required: true,
  },
});

const currentUser = auth?.user;

// Ambil data dari prop users (Laravel pagination object)
const users = computed(() => usersProp.data);
const totalItems = computed(() => usersProp.total);
const itemsPerPage = computed(() => usersProp.per_page);
const currentPage = computed(() => usersProp.current_page);

const loading = ref(false);
const showDeleted = ref(false);

function loadItems({ page, itemsPerPage: perPage }) {
  loading.value = true;
  router.get(
    route('users'),
    { page, per_page: perPage, show_deleted: showDeleted.value },
    {
      replace: true,
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        loading.value = false;
      },
    },
  );
}

async function deleteUser(id) {
  router.delete(route('users.delete', id), {preserveScroll: true});
}

async function restoreUser(id) {
  // Implementasi restore user - kamu yang bikin
  router.patch(route('users.restore', id), {}, {preserveScroll: true});
}

function isCurrentUser(userId) {
  return currentUser && currentUser.id === userId;
}
</script>

<template>
  <Head title="Pengguna"></Head>
  <v-container>
    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Kelola"
          second-part-title="Pengguna"
        />
      </v-col>
    </v-row>
    <v-row>
      <v-col class="flex justify-between items-center">
        <v-btn
          variant="tonal"
          color="blue-darken-2"
        >
          <span>
            <v-icon icon="mdi-account-plus" />
            Tambah pengguna
          </span>
          <CreateUpdateUserForm
            title="Tambah Pengguna Baru"
            :url="route('users.create')"
            mode="create"
            activator="parent"
          />
        </v-btn>
        
        <v-switch
          v-model="showDeleted"
          color="blue-darken-2"
          label="Tampilkan yang terhapus"
          hide-details
          @update:model-value="loadItems({ page: 1, itemsPerPage })"
        />
      </v-col>
    </v-row>

    <!-- Desktop Table View -->
    <v-row class="hidden! md:block!">
      <v-col>
        <v-data-table-server
          :headers="[
            { title: 'No', key: 'no' },
            { title: 'Nama', key: 'name' },
            { title: 'Username', key: 'username' },
            { title: 'Role', key: 'role.name' },
            { title: 'No. Telepon', key: 'telepon' },
            ...(showDeleted ? [{ title: 'Dihapus Pada', key: 'deleted_at' }] : []),
            { title: 'More', key: 'more' },
          ]"
          :items="users"
          :items-length="totalItems"
          :loading="loading"
          :items-per-page="itemsPerPage"
          :page="currentPage"
          class="hidden! md:block!"
          @update:options="loadItems"
        >
          <template #headers="{ headers }">
            <tr class="bg-blue-darken-2">
              <th class="w-1/16">No</th>
              <th>Nama</th>
              <th>Username</th>
              <th>No. telepon</th>
              <th>Role</th>
              <th v-if="showDeleted">Dihapus Pada</th>
              <th class="w-1/12">Tindakan</th>
            </tr>
          </template>
          <template #item="{ item }">
            <tr :class="{ 'bg-red-lighten-4': item.deleted_at}">
              <td>{{ item.no }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.username }}</td>
              <td>{{ item.telepon || '-'}}</td>
              <td>{{ item.role?.name }}</td>
              <td v-if="showDeleted">
                <span v-if="item.deleted_at" class="text-medium!">
                  {{ new Date(item.deleted_at).toLocaleString('id-ID').replaceAll('/', '-').replaceAll('.', ':').replace(',', '') }}
                </span>
                <span v-else>-</span>
              </td>
              <td>
                <v-btn
                  variant="text"
                  icon
                >
                  <v-icon icon="mdi-dots-vertical" />
                  <v-menu activator="parent">
                    <v-list density="compact">
                      <v-list-item
                        v-if="!item.deleted_at"
                        value="edit"
                        :disabled="isCurrentUser(item.id)"
                      >
                        <v-icon
                          icon="mdi-pencil"
                          class="mr-2"
                        />
                        Edit
                        <CreateUpdateUserForm
                          mode="edit"
                          title="Edit pengguna"
                          :url="route('users.update', item.id)"
                          activator="parent"
                          :initial-value="{
                            name: item.name,
                            username: item.username,
                            password: '',
                            role: item.role_id,
                            telepon: item.telepon,
                          }"
                        />
                      </v-list-item>
                      <v-list-item
                        v-if="!item.deleted_at"
                        value="delete"
                        :disabled="isCurrentUser(item.id)"
                      >
                        <v-icon
                          icon="mdi-delete"
                          class="mr-2"
                        />
                        Hapus
                        <v-dialog
                          v-slot="{ isActive }"
                          activator="parent"
                          max-width="400"
                        >
                          <v-card>
                            <v-card-title class="bg-blue-darken-2 text-center text-wrap">Konfirmasi!</v-card-title>
                            <v-card-text>
                              <div>
                                Apakah yakin untuk menghapus pengguna dengan nama <span class="text-blue-600">{{ item.name }}</span>
                              </div>
                            </v-card-text>
                            <v-card-actions>
                              <v-btn
                                @click="
                                  deleteUser(item.id);
                                  isActive.value = false;
                                "
                                >Ya</v-btn
                              >
                              <v-btn @click="isActive.value = false">Batal</v-btn>
                            </v-card-actions>
                          </v-card>
                        </v-dialog>
                      </v-list-item>
                      <v-list-item
                        v-if="item.deleted_at"
                        value="restore"
                      >
                        <v-icon
                          icon="mdi-restore"
                          class="mr-2"
                        />
                        Pulihkan
                        <v-dialog
                          v-slot="{ isActive }"
                          activator="parent"
                          max-width="400"
                        >
                          <v-card>
                            <v-card-title class="bg-blue-darken-2 text-center text-wrap">Konfirmasi!</v-card-title>
                            <v-card-text>
                              <div>
                                Apakah yakin untuk memulihkan pengguna dengan nama <span class="text-blue-600">{{ item.name }}</span>?
                              </div>
                            </v-card-text>
                            <v-card-actions>
                              <v-btn
                                @click="
                                  restoreUser(item.id);
                                  isActive.value = false;
                                "
                                >Ya</v-btn
                              >
                              <v-btn @click="isActive.value = false">Batal</v-btn>
                            </v-card-actions>
                          </v-card>
                        </v-dialog>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </v-btn>
              </td>
            </tr>
          </template>
        </v-data-table-server>
      </v-col>
    </v-row>

    <!-- Mobile Card View -->
    <v-row class="md:hidden!">
      <v-col>
        <v-progress-circular
          v-if="loading"
          indeterminate
          class="d-block mx-auto my-4"
        />
        <v-row
          v-for="user in users"
          :key="user.id"
        >
          <v-col>
            <v-card :class="{ 'bg-red-accent-1': user.deleted_at }">
              <v-card-actions class="bg-blue-darken-2 flex justify-end">
                <v-btn
                  variant="text"
                  icon
                >
                  <v-icon icon="mdi-dots-vertical" />
                  <v-menu activator="parent">
                    <v-list density="compact">
                      <v-list-item
                        v-if="!user.deleted_at"
                        value="edit"
                        :disabled="isCurrentUser(user.id)"
                      >
                        <v-icon
                          icon="mdi-pencil"
                          class="mr-2"
                        />
                        Sunting
                        <CreateUpdateUserForm
                          mode="edit"
                          title="Edit pengguna"
                          :url="route('users.update', user.id)"
                          activator="parent"
                          :initial-value="{
                            name: user.name,
                            username: user.username,
                            password: '',
                            role: user.role_id,
                            telepon: user.telepon,
                          }"
                        />
                      </v-list-item>
                      <v-list-item
                        v-if="!user.deleted_at"
                        value="delete"
                        :disabled="isCurrentUser(user.id)"
                      >
                        <v-icon
                          icon="mdi-delete"
                          class="mr-2"
                        />
                        Hapus
                        <v-dialog
                          v-slot="{ isActive }"
                          activator="parent"
                          max-width="400"
                        >
                          <v-card>
                            <v-card-title class="bg-blue-darken-2 text-center text-wrap">Konfirmasi!</v-card-title>
                            <v-card-text>
                              <div>
                                Apakah yakin untuk menghapus pengguna dengan nama <span class="text-blue-600">{{ user.name }}</span>
                              </div>
                            </v-card-text>
                            <v-card-actions>
                              <v-btn
                                @click="
                                  deleteUser(user.id);
                                  isActive.value = false;
                                "
                                >Ya</v-btn
                              >
                              <v-btn @click="isActive.value = false">Batal</v-btn>
                            </v-card-actions>
                          </v-card>
                        </v-dialog>
                      </v-list-item>
                      <v-list-item
                        v-if="user.deleted_at"
                        value="restore"
                      >
                        <v-icon
                          icon="mdi-restore"
                          class="mr-2"
                        />
                        Pulihkan
                        <v-dialog
                          v-slot="{ isActive }"
                          activator="parent"
                          max-width="400"
                        >
                          <v-card>
                            <v-card-title class="bg-blue-darken-2 text-center text-wrap">Konfirmasi!</v-card-title>
                            <v-card-text>
                              <div>
                                Apakah yakin untuk memulihkan pengguna dengan nama <span class="text-blue-600">{{ user.name }}</span>?
                              </div>
                            </v-card-text>
                            <v-card-actions>
                              <v-btn
                                @click="
                                  restoreUser(user.id);
                                  isActive.value = false;
                                "
                                >Ya</v-btn
                              >
                              <v-btn @click="isActive.value = false">Batal</v-btn>
                            </v-card-actions>
                          </v-card>
                        </v-dialog>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </v-btn>
              </v-card-actions>
              <v-card-text>
                <v-row>
                  <v-col cols="5">Nama Lengkap</v-col>
                  <v-col>{{ user.name }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Username</v-col>
                  <v-col>{{ user.username }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">No. Telepon</v-col>
                  <v-col>{{ user.telepon || '-' }}</v-col>
                </v-row>
                <v-row>
                  <v-col cols="5">Role</v-col>
                  <v-col>{{ user.role?.name }}</v-col>
                </v-row>
                <v-row v-if="showDeleted && user.deleted_at">
                  <v-col cols="5">Terhapus Pada</v-col>
                  <v-col>{{ new Date(user.deleted_at).toLocaleString('id-ID') }}</v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Mobile Pagination -->
        <v-row v-if="!loading && users.length > 0">
          <v-col>
            <v-pagination
              :model-value="currentPage"
              :length="Math.ceil(totalItems / itemsPerPage)"
              total-visible="5"
              @update:model-value="(page) => loadItems({ page, itemsPerPage })"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>
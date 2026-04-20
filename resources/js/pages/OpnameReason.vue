<script setup>
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import DeleteActionVListItem from '@/components/organisms/DeleteActionVListItem.vue';
import OpnameReasonCreateFormDialog from '@/components/organisms/OpnameReasonCreateFormDialog.vue';
import OpnameReasonEditVListItem from '@/components/organisms/OpnameReasonEditVListItem.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  reasons: {
    type: Array,
    default: () => [],
  },
});

const errorDialog = ref({
  title: null,
  message: null,
  show: false,
});
const search = ref('');

const filteredReasons = computed(function () {
  const s = String(search.value).trim();
  if (!s) {
    return props.reasons;
  }
  return props.reasons.filter((reason) => RegExp(s, 'i').test(reason.reason));
});

function closeErrorDialog() {
  errorDialog.value.show = false;
  errorDialog.value.title = null;
  errorDialog.value.message = null;
}

function openErrorDialog(title, message) {
  errorDialog.value.show = true;
  errorDialog.value.title = title;
  errorDialog.value.message = message;
}
</script>

<template>
  <Head>
    <title>Kelola Sebab Stock Opname</title>
  </Head>
  <AlertDialog :errorDialog />
  <v-container>
    <v-row>
      <v-col>
        <PageTitleHighlightPart
          first-part-title="Kelola"
          second-part-title="Sebab Opname" />
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-btn
          prepend-icon="mdi-plus"
          variant="tonal"
          color="primary">
          <span>Tambah Sebab</span>
          <OpnameReasonCreateFormDialog />
        </v-btn>
      </v-col>
      <v-col>
        <v-text-field
          v-model="search"
          label="Cari..."
          variant="outlined"
          density="compact"
          color="primary" />
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-table>
          <thead class="bg-blue-darken-2 font-semibold">
            <tr>
              <th class="w-1/12">No</th>
              <th>Sebab</th>
              <th class="w-1/12 text-left">Tindakan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="reasons.length <= 0">
              <td
                colspan="12"
                class="text-center">
                <span>Belum ada sebab stock opname yang ditambahkan</span>
              </td>
            </tr>
            <tr v-if="filteredReasons.length <= 0">
              <td
                colspan="12"
                class="text-center">
                <span>Tidak ada sebab stock opname yang ditemukan</span>
              </td>
            </tr>
            <template v-else>
              <tr
                v-for="(opnameReason, index) in filteredReasons"
                :key="index">
                <td>{{ index + 1 }}</td>
                <td>{{ opnameReason.reason }}</td>
                <td class="flex cursor-pointer items-center justify-end">
                  <v-btn
                    variant="text"
                    size="small"
                    icon="mdi-dots-vertical" />
                  <v-menu
                    activator="parent"
                    close-on-content-click>
                    <v-list
                      density="compact"
                      class="py-0!"
                      :elevation="1">
                      <OpnameReasonEditVListItem
                        :reason="opnameReason.reason"
                        :opname-reason-id="opnameReason.id" />
                      <DeleteActionVListItem
                        :name="opnameReason.reason"
                        :delete-url="route('opname.reasons.delete', opnameReason.id)"
                        @open-error-dialog="openErrorDialog"
                        @close-error-dialog="closeErrorDialog" />
                    </v-list>
                  </v-menu>
                </td>
              </tr>
            </template>
          </tbody>
        </v-table>
      </v-col>
    </v-row>
  </v-container>
</template>

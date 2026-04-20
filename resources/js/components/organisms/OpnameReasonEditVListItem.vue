<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  opnameReasonId: {
    type: String,
    required: true,
  },
  reason: {
    type: String,
    required: true,
  },
});

const form = useForm({
  reason: props.reason,
});
const showFormDialog = ref(false);

function handleSubmit() {
  form.put(route('opname.reasons.update', props.opnameReasonId), {
    preserveScroll: true,
    onSuccess() {
      closeFormDialog();
    },
  });
}

function closeFormDialog() {
  showFormDialog.value = false;
}
</script>
<template>
  <v-list-item value="edit">  
    <v-icon
      icon="mdi-pencil"
      class="me-2!" />
    Edit
    <v-dialog
      activator="parent"
      v-model="showFormDialog">
      <v-form @submit.prevent="handleSubmit">
        <v-card>
          <v-card-title class="bg-blue-darken-2 text-center">Edit Sebab Opname</v-card-title>
          <v-card-text>
            <v-text-field
              label="Sebab"
              variant="outlined"
              v-model="form.reason" />
          </v-card-text>
          <v-card-actions>
            <v-btn type="submit">Simpan</v-btn>
            <v-btn @click="closeFormDialog">Batal</v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>
  </v-list-item>
</template>

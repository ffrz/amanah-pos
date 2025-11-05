<script setup>
import { ref, computed } from "vue";
import { useQuasar } from "quasar";
import SupplierForm from "@/components/SupplierForm.vue";
import { useApiForm } from "@/composables/useApiForm";
import { handleLoadForm, handleSubmit } from "@/helpers/client-req-handler";

const $q = useQuasar();
const showDialog = ref(false);
const simpleMode = ref(true);

const form = useApiForm({
  id: null,
  code: null,
  name: "",
  phone_1: null,
  phone_2: null,
  phone_3: null,
  url_1: null,
  url_2: null,
  bank_account_name_1: null,
  bank_account_holder_1: null,
  bank_account_number_1: null,
  bank_account_name_2: null,
  bank_account_holder_2: null,
  bank_account_number_2: null,
  address: null,
  return_address: null,
  active: true,
});

const formErrors = ref({});

const emit = defineEmits(["supplierCreated"]);

const isFullScreen = computed(() => {
  return $q.screen.lt.sm;
});

const open = async () => {
  await handleLoadForm({
    form,
    url: route("admin.supplier.add"),
  });
  showDialog.value = true;
};

const submit = async () => {
  handleSubmit({
    form,
    url: route("admin.supplier.save"),
    onSuccess: (response) => {
      showDialog.value = false;
      emit("supplierCreated", response.data);
    },
  });
};

defineExpose({ open });
</script>

<template>
  <q-dialog
    v-model="showDialog"
    fullscreen
    persistent
    :fullscreen="isFullScreen"
  >
    <q-card :style="isFullScreen ? '' : 'width: 600px; max-width: 90vw'">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-subtitle1 text-grey-8">Tambah Pemasok Baru</div>
        <q-space />
        <q-btn
          icon="close"
          flat
          round
          dense
          v-close-popup
          :disable="form.processing"
          size="sm"
        />
      </q-card-section>

      <q-card-section class="q-pt-md">
        <SupplierForm
          :dialog-mode="true"
          :form="form"
          :simple-mode="simpleMode"
          @update:simpleMode="simpleMode = $event"
          @submit="submit"
          @validation-error="
            $q.notify({
              type: 'negative',
              message: 'Terdapat error di form. Silakan periksa.',
            })
          "
        />
      </q-card-section>

      <q-card-actions align="right" class="q-pa-md">
        <q-btn
          label="Batal"
          color="grey"
          flat
          v-close-popup
          :disable="form.processing"
        />

        <q-btn
          label="Simpan"
          type="submit"
          color="primary"
          :loading="form.processing"
          @click="submit"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

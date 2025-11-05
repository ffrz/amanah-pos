<script setup>
import { ref, computed } from "vue";
import { useQuasar } from "quasar";
import axios from "axios";
import SupplierForm from "@/components/SupplierForm.vue";

const $q = useQuasar();
const isModalOpen = ref(false);
const isLoading = ref(false);
const simpleMode = ref(true);

const form = ref({
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

const open = async (query) => {
  isLoading.value = true;
  isModalOpen.value = true;
  simpleMode.value = true;
  try {
    // Asumsi rute untuk data awal add/edit supplier adalah 'admin.supplier.add'
    const response = await axios.get(route("admin.supplier.add"));
    form.value = { ...response.data.data };
    formErrors.value = {};
  } catch (error) {
    console.error("Quick Create Failed:", error.response);
    $q.notify({
      type: "negative",
      message: "Gagal mengambil data awal pemasok.",
    });
  } finally {
    isLoading.value = false;
  }
};
defineExpose({ open });

const submitQuickCreate = async () => {
  isLoading.value = true;
  formErrors.value = {};

  try {
    // Asumsi rute untuk menyimpan supplier adalah 'admin.supplier.save'
    const response = await axios.post(route("admin.supplier.save"), form.value);

    $q.notify({
      type: "positive",
      message: `Pemasok ${response.data.data.name} berhasil ditambahkan!`,
    });
    isModalOpen.value = false;

    emit("supplierCreated", response.data.data);
  } catch (error) {
    console.error("Quick Create Failed:", error.response);
    let errorMessage = "Gagal menambahkan pemasok.";

    if (error.response && error.response.status === 422) {
      formErrors.value = error.response.data.errors || {};
      errorMessage = "Formulir tidak valid. Cek kembali input Anda.";
    } else if (error.response) {
      errorMessage = error.response.data.message || errorMessage;
    }

    $q.notify({
      type: "negative",
      message: errorMessage,
    });
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <q-dialog
    v-model="isModalOpen"
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
          :disable="isLoading"
          size="sm"
        />
      </q-card-section>

      <q-card-section class="q-pt-md">
        <SupplierForm
          :dialog-mode="true"
          :form-data="form"
          :form-errors="formErrors"
          :processing="isLoading"
          :simple-mode="simpleMode"
          @update:simpleMode="simpleMode = $event"
          @submit="submitQuickCreate"
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
          :disable="isLoading"
        />

        <q-btn
          label="Simpan"
          type="submit"
          color="primary"
          :loading="isLoading"
          @click="submitQuickCreate"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

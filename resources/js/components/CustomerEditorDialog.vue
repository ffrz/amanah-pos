<script setup>
import { ref, computed } from "vue";
import { useQuasar } from "quasar";
import axios from "axios";
import CustomerForm from "@/components/CustomerForm.vue";

const $q = useQuasar();
const isModalOpen = ref(false);
const isLoading = ref(false);
const simpleMode = ref(true);

const form = ref({
  id: null,
  code: null,
  name: "",
  default_price_type: "price_1",
  phone: null,
  address: null,
  active: true,
  password: "12345",
  type: "general",
});

const formErrors = ref({});

const emit = defineEmits(["customerCreated"]);

const isFullScreen = computed(() => {
  return $q.screen.lt.sm;
});

const open = async (query) => {
  isLoading.value = true;
  isModalOpen.value = true;
  try {
    const response = await axios.get(route("admin.customer.add"));
    form.value = { ...response.data.data };
    formErrors.value = {};
  } catch (error) {
    console.error("Quick Create Failed:", error.response);
    $q.notify({
      type: "negative",
      message: "Gagal mengambil data awal pelanggan.",
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
    const response = await axios.post(route("admin.customer.save"), form.value);

    $q.notify({
      type: "positive",
      message: `Pelanggan ${response.data.data.name} berhasil ditambahkan!`,
    });
    isModalOpen.value = false;

    emit("customerCreated", response.data.data);
  } catch (error) {
    console.error("Quick Create Failed:", error.response);
    let errorMessage = "Gagal menambahkan pelanggan.";

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
        <div class="text-subtitle1 text-grey-8">Tambah Pelanggan Baru</div>
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
        <CustomerForm
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

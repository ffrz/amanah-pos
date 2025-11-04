<script setup>
import { ref, computed } from "vue"; // Tambahkan 'computed'
import { useQuasar } from "quasar";
import axios from "axios";
import CustomerForm from "@/components/CustomerForm.vue";

const $q = useQuasar();
const isModalOpen = ref(false);
const isSubmitting = ref(false);
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

// Local error state
const formErrors = ref({});

const emit = defineEmits(["customerCreated"]);

// --- Logika Penambahan ---
// Computed property untuk menentukan apakah dialog harus fullscreen
const isFullScreen = computed(() => {
  return true;
  // Jika lebar layar kurang dari breakpoint 'sm' (biasanya < 600px), setel fullscreen=true
  return $q.screen.lt.sm;
});
// --- Akhir Logika Penambahan ---

const open = (query) => {
  form.value.name = query || "";
  form.value.code = "";
  form.value.phone = "";
  formErrors.value = {};
  isModalOpen.value = true;
};
defineExpose({ open });

const submitQuickCreate = async () => {
  isSubmitting.value = true;
  formErrors.value = {};

  try {
    const response = await axios.post(
      route("api.customer.quick-save"),
      form.value
    );

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
    isSubmitting.value = false;
  }
};
</script>

<template>
  <!-- Gunakan prop :fullscreen yang diikat ke computed property isFullScreen -->

  <q-dialog
    v-model="isModalOpen"
    fullscreen
    persistent
    :fullscreen="isFullScreen"
  >
    <!-- Atur ukuran card hanya jika tidak dalam mode fullscreen (desktop/tablet) -->

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
          :disable="isSubmitting"
          size="sm"
        />
      </q-card-section>

      <q-card-section class="q-pt-md">
        <CustomerForm
          :bordered="false"
          :form-data="form"
          :form-errors="formErrors"
          :processing="isSubmitting"
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
          :disable="isSubmitting"
        />

        <q-btn
          label="Simpan"
          type="submit"
          color="primary"
          :loading="isSubmitting"
          @click="submitQuickCreate"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

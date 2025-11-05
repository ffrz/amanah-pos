<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import ProductCategoryForm from "./ProductCategoryForm.vue";

const $q = useQuasar();
const isModalOpen = ref(false);
const formRef = ref(null);
const initialValue = {
  id: null,
  name: null,
  description: null,
};
const form = ref({ ...initialValue });
const emit = defineEmits(["categoryCreated"]);
const isLoading = ref(false);
const formErrors = ref({});
const show = (initialData = {}) => {
  form.id = initialData.id || null;
  form.name = initialData.name || null;
  form.description = initialData.description || null;

  isModalOpen.value = true;
};

const hide = () => {
  isModalOpen.value = false;
};

const submit = async () => {
  isLoading.value = true;
  formErrors.value = {};

  try {
    const response = await axios.post(
      route("admin.product-category.save"),
      form.value
    );

    $q.notify({
      type: "positive",
      message: `Kategori produk ${response.data.data.name} berhasil ditambahkan!`,
    });
    isModalOpen.value = false;

    emit("categoryCreated", response.data.data);
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

defineExpose({
  show,
  hide,
});
</script>

<template>
  <q-dialog v-model="isModalOpen" persistent>
    <q-card style="width: 400px; max-width: 90vw">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-subtitle1 text-grey-8">Tambah Kategori Produk</div>
        <q-space />
        <q-btn
          icon="close"
          flat
          round
          dense
          :disable="form.processing"
          @click="hide"
          size="sm"
        />
      </q-card-section>

      <q-card-section class="q-pt-md">
        <product-category-form
          ref="formRef"
          :form-data="form"
          :form-errors="formErrors"
          :processing="isLoading"
          dialog-mode
          @submit="submit"
          @cancel="hide"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

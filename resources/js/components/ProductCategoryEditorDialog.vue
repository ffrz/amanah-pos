<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import ProductCategoryForm from "./ProductCategoryForm.vue";
import { handleSubmit } from "@/helpers/client-req-handler";
import { useApiForm } from "@/composables/useApiForm";

const showDialog = ref(false);
const form = useApiForm({
  id: null,
  name: null,
  description: null,
});
const emit = defineEmits(["categoryCreated"]);
const show = () => {
  form.reset();
  form.clearErrors();
  showDialog.value = true;
};

const hide = () => {
  showDialog.value = false;
};

const submit = async () => {
  handleSubmit({
    form,
    url: route("admin.product-category.save"),
    onSuccess: (response) => {
      emit("categoryCreated", response.data);
      hide();
    },
  });
};

defineExpose({
  show,
  hide,
});
</script>

<template>
  <q-dialog v-model="showDialog" persistent>
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
          :form="form"
          dialog-mode
          @submit="submit"
          @cancel="hide"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

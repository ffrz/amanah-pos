<script setup>
import { ref } from "vue";
import ProductBrandForm from "./ProductBrandForm.vue";
import { handleSubmit } from "@/helpers/client-req-handler";
import { useApiForm } from "@/composables/useApiForm";

const showDialog = ref(false);
const form = useApiForm({
  id: null,
  active: true,
  name: null,
});
const emit = defineEmits(["itemCreated"]);
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
    url: route("admin.product-brand.save"),
    onSuccess: (response) => {
      emit("itemCreated", response.data);
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
        <div class="text-subtitle1 text-grey-8">Tambah Merk</div>
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
        <product-brand-form
          :form="form"
          dialog-mode
          @submit="submit"
          @cancel="hide"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

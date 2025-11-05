<script setup>
import { router } from "@inertiajs/vue3";
import { computed } from "vue";
import { scrollToFirstErrorField } from "@/helpers/utils";

const props = defineProps({
  formData: {
    type: Object,
    required: true,
  },
  formErrors: {
    type: Object,
    required: true,
  },
  dialogMode: {
    type: Boolean,
    default: false,
  },
  processing: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "update:modelValue",
  "submit",
  "validationError",
  "cancel",
]);

const processedErrors = computed(() => {
  const finalErrors = {};
  if (props.dialogMode) {
    for (const key in props.formErrors) {
      const errorValue = props.formErrors[key];
      if (Array.isArray(errorValue) && errorValue.length > 0) {
        finalErrors[key] = errorValue[0];
      } else if (typeof errorValue === "string") {
        finalErrors[key] = errorValue;
      }
    }
  } else {
    return props.formErrors;
  }
  return finalErrors;
});

const getErrorMessage = (key) => {
  return processedErrors.value[key] || null;
};
</script>

<template>
  <q-form
    class="row"
    @submit.prevent="$emit('submit')"
    @validation-error="scrollToFirstErrorField"
  >
    <q-card
      square
      flat
      bordered
      class="col"
      :class="{ 'no-shadow no-border': dialogMode }"
    >
      <q-card-section :class="{ 'q-pa-none': dialogMode }">
        <input type="hidden" name="id" v-model="props.formData.id" />

        <q-input
          autofocus
          v-model.trim="props.formData.name"
          label="Nama Kategori"
          lazy-rules
          :disable="props.processing"
          :error="!!getErrorMessage('name')"
          :error-message="getErrorMessage('name')"
          :rules="[(val) => (val && val.length > 0) || 'Nama harus diisi.']"
          hide-bottom-space
          class="q-mb-md"
        />

        <q-input
          v-model.trim="props.formData.description"
          type="textarea"
          autogrow
          counter
          maxlength="200"
          label="Deskripsi"
          lazy-rules
          :disable="props.processing"
          :error="!!getErrorMessage('description')"
          :error-message="getErrorMessage('description')"
          hide-bottom-space
        />
      </q-card-section>

      <q-card-section
        class="q-gutter-sm"
        :class="dialogMode ? 'q-pa-none' : ''"
      >
        <q-btn
          icon="save"
          type="submit"
          label="Simpan"
          color="primary"
          :disable="props.processing"
          :loading="props.processing"
        />
        <q-btn
          icon="cancel"
          label="Batal"
          :disable="props.processing"
          @click.stop="
            dialogMode
              ? $emit('cancel')
              : router.get(route('admin.product-category.index'))
          "
        />
      </q-card-section>
    </q-card>
  </q-form>
</template>

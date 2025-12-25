<script setup>
import { router } from "@inertiajs/vue3";
import { scrollToFirstErrorField } from "@/helpers/utils";
import StandardCheckBox from "./StandardCheckBox.vue";

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  dialogMode: {
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
        <input type="hidden" name="id" v-model="form.id" />

        <q-input
          autofocus
          v-model.trim="form.name"
          label="Nama Merk"
          lazy-rules
          :disable="form.processing"
          :error="!!form.errors.name"
          :error-message="form.errors.name"
          :rules="[(val) => (val && val.length > 0) || 'Nama harus diisi.']"
          hide-bottom-space
          class="q-mb-md"
        />

        <standard-check-box
          v-model="form.active"
          label="Aktif"
          :disable="form.processing"
        />
      </q-card-section>

      <q-card-section
        class="q-gutter-sm"
        :class="dialogMode ? 'q-pa-none q-pt-md' : ''"
      >
        <q-btn
          type="submit"
          label="Simpan"
          color="primary"
          :disable="form.processing"
          :loading="form.processing"
        />
        <q-btn
          label="Batal"
          :disable="form.processing"
          @click.stop="
            dialogMode
              ? $emit('cancel')
              : router.get(route('admin.product-brand.index'))
          "
        />
      </q-card-section>
    </q-card>
  </q-form>
</template>

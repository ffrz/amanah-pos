<script setup>
import { computed } from "vue";
import StandardCheckBox from "@/components/StandardCheckBox.vue";

const props = defineProps({
  dialogMode: {
    type: Boolean,
    default: false,
  },
  formData: {
    type: Object,
    required: true,
  },
  processing: {
    type: Boolean,
    default: false,
  },
  formErrors: {
    type: Object,
    default: () => ({}),
  },
  simpleMode: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["submit", "validationError", "update:simpleMode"]);

const errors = computed(() => {
  if (props.dialogMode) {
    const newErrors = {};
    for (const key in props.formErrors) {
      if (
        Array.isArray(props.formErrors[key]) &&
        props.formErrors[key].length > 0
      ) {
        newErrors[key] = props.formErrors[key][0];
      }
    }
    return newErrors;
  }

  return props.formErrors;
});

const toggleSimpleMode = () => {
  emit("update:simpleMode", !props.simpleMode);
};
</script>

<template>
  <q-form
    class="row"
    @submit.prevent="$emit('submit')"
    @validation-error="$emit('validationError', $event)"
  >
    <input type="hidden" name="id" v-model="props.formData.id" />
    <q-card square flat class="col" :bordered="!dialogMode">
      <q-card-section :class="dialogMode ? 'q-pa-none' : 'q-pt-none'">
        <q-input
          autofocus
          v-model.trim="props.formData.code"
          label="Kode Pemasok"
          :error="!!errors.code"
          :disable="props.processing"
          :error-message="errors.code"
          :rules="[(val) => (val && val.length > 0) || 'Kode harus diisi.']"
          lazy-rules
          hide-bottom-space
        />
        <q-input
          v-model.trim="props.formData.name"
          label="Nama Pemasok"
          :error="!!errors.name"
          :disable="props.processing"
          :error-message="errors.name"
          :rules="[(val) => (val && val.length > 0) || 'Nama harus diisi.']"
          lazy-rules
          hide-bottom-space
        />
        <div class="row q-col-gutter-md">
          <q-input
            v-model.trim="props.formData.phone_1"
            type="text"
            label="No Telp"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.phone_1"
            :error-message="errors.phone_1"
            hide-bottom-space
            class="col"
          />
          <q-input
            v-if="!simpleMode"
            v-model.trim="props.formData.phone_2"
            type="text"
            label="No Telp 2"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.phone_2"
            :error-message="errors.phone_2"
            hide-bottom-space
            class="col"
          />
          <q-input
            v-if="!simpleMode"
            v-model.trim="props.formData.phone_3"
            type="text"
            label="No Telp 3"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.phone_3"
            :error-message="errors.phone_3"
            hide-bottom-space
            class="col"
          />
        </div>
        <q-input
          v-model.trim="props.formData.address"
          type="textarea"
          autogrow
          counter
          maxlength="500"
          label="Alamat"
          lazy-rules
          :disable="props.processing"
          :error="!!errors.address"
          :error-message="errors.address"
          hide-bottom-space
        />
        <q-input
          v-if="!simpleMode"
          v-model.trim="props.formData.return_address"
          type="textarea"
          autogrow
          counter
          maxlength="500"
          label="Alamat Retur"
          lazy-rules
          :disable="props.processing"
          :error="!!errors.return_address"
          :error-message="errors.return_address"
          hide-bottom-space
        />

        <!-- Rekening 1 -->
        <div v-if="!simpleMode" class="text-subtitle2 q-mt-md text-grey-8">
          Rekening 1
        </div>
        <div class="row q-col-gutter-md" v-if="!simpleMode">
          <q-input
            v-model.trim="props.formData.bank_account_name_1"
            type="text"
            label="Nama Bank"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.bank_account_name_1"
            :error-message="errors.bank_account_name_1"
            hide-bottom-space
            class="col-4"
          />
          <q-input
            v-model.trim="props.formData.bank_account_number_1"
            type="text"
            label="Nomor Rekening"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.bank_account_number_1"
            :error-message="errors.bank_account_number_1"
            hide-bottom-space
            class="col-4"
          />
          <q-input
            v-model.trim="props.formData.bank_account_holder_1"
            type="text"
            label="Atas Nama"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.bank_account_holder_1"
            :error-message="errors.bank_account_holder_1"
            hide-bottom-space
            class="col-4"
          />
        </div>

        <!-- Rekening 2 -->
        <div v-if="!simpleMode" class="text-subtitle2 q-mt-md text-grey-8">
          Rekening 2
        </div>
        <div class="row q-col-gutter-md" v-if="!simpleMode">
          <q-input
            v-model.trim="props.formData.bank_account_name_2"
            type="text"
            label="Nama Bank"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.bank_account_name_2"
            :error-message="errors.bank_account_name_2"
            hide-bottom-space
            class="col-4"
          />
          <q-input
            v-model.trim="props.formData.bank_account_number_2"
            type="text"
            label="Nomor Rekening"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.bank_account_number_2"
            :error-message="errors.bank_account_number_2"
            hide-bottom-space
            class="col-4"
          />
          <q-input
            v-model.trim="props.formData.bank_account_holder_2"
            type="text"
            label="Atas Nama"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.bank_account_holder_2"
            :error-message="errors.bank_account_holder_2"
            hide-bottom-space
            class="col-4"
          />
        </div>

        <!-- URLs -->
        <div class="row q-col-gutter-md" v-if="!simpleMode">
          <q-input
            v-model.trim="props.formData.url_1"
            type="text"
            label="URL 1"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.url_1"
            :error-message="errors.url_1"
            hide-bottom-space
            class="col"
          />
          <q-input
            v-model.trim="props.formData.url_2"
            type="text"
            label="URL 2"
            lazy-rules
            :disable="props.processing"
            :error="!!errors.url_2"
            :error-message="errors.url_2"
            hide-bottom-space
            class="col"
          />
        </div>

        <!-- Checkbox Aktif -->
        <StandardCheckBox
          v-model="props.formData.active"
          label="Aktif"
          :disable="props.processing"
        />

        <!-- Tombol Toggle Mode -->
        <div class="q-mt-md">
          <div
            class="cursor-pointer text-grey-8"
            clickable
            @click.stop="toggleSimpleMode"
          >
            <q-icon
              :name="simpleMode ? 'stat_minus_1' : 'stat_1'"
              class="inline-icon"
            />
            {{ simpleMode ? "Lebih lengkap" : "Lebih ringkas" }}
          </div>
        </div>
      </q-card-section>
    </q-card>
    <input type="submit" style="display: none" />
  </q-form>
</template>

<script setup>
import { computed, ref } from "vue";
import { createOptions } from "@/helpers/options";
import StandardCheckBox from "@/components/StandardCheckBox.vue";
import { useFormError } from "@/composables/useFormError";

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

const emit = defineEmits(["submit", "update:simpleMode"]);

const { getErrorMessage } = useFormError(
  () => props.formErrors,
  () => props.dialogMode
);

const types = createOptions(window.CONSTANTS.CUSTOMER_TYPES);
const priceOptions = createOptions(window.CONSTANTS.PRODUCT_PRICE_TYPES);
const showPassword = ref(!props.formData.id ? true : false);
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
          label="Kode Pelanggan"
          :error="!!getErrorMessage('code')"
          :disable="props.processing"
          :error-message="getErrorMessage('code')"
          :rules="[(val) => (val && val.length > 0) || 'Kode harus diisi.']"
          lazy-rules
          hide-bottom-space
        />
        <q-input
          v-model.trim="props.formData.name"
          label="Nama Pelanggan"
          :error="!!getErrorMessage('name')"
          :disable="props.processing"
          :error-message="getErrorMessage('name')"
          :rules="[(val) => (val && val.length > 0) || 'Nama harus diisi.']"
          lazy-rules
          hide-bottom-space
        />
        <q-select
          v-model="props.formData.default_price_type"
          :options="priceOptions"
          label="Tipe Harga"
          map-options
          emit-value
          hide-bottom-space
          :error="!!getErrorMessage('default_price_type')"
          :error-message="getErrorMessage('default_price_type')"
          :disable="props.processing"
          transition-show="jump-up"
          transition-hide="jump-up"
        />
        <q-select
          v-if="!simpleMode"
          v-model="props.formData.type"
          label="Jenis Akun"
          :options="types"
          map-options
          emit-value
          lazy-rules
          :disable="props.processing"
          transition-show="jump-up"
          transition-hide="jump-up"
          :error="!!getErrorMessage('type')"
          :error-message="getErrorMessage('type')"
          hide-bottom-space
        />
        <q-input
          v-model.trim="props.formData.phone"
          type="text"
          label="No HP"
          :disable="props.processing"
          :error="!!getErrorMessage('phone')"
          :error-message="getErrorMessage('phone')"
          lazy-rules
          hide-bottom-space
        />
        <q-input
          v-model.trim="props.formData.address"
          type="textarea"
          maxlength="200"
          label="Alamat"
          :disable="props.processing"
          :error="!!getErrorMessage('address')"
          :error-message="getErrorMessage('address')"
          autogrow
          counter
          lazy-rules
          hide-bottom-space
        />
        <StandardCheckBox
          v-model="props.formData.active"
          label="Aktif"
          :disable="props.processing"
        />
        <q-input
          v-if="!simpleMode"
          autocomplete="off"
          aria-autocomplete="none"
          v-model="props.formData.password"
          :label="
            !props.formData.id
              ? 'Kata Sandi Baru (Wajib diisi)'
              : 'Kata Sandi (Isi jika ingin mengganti)'
          "
          :disable="props.processing"
          :error="!!getErrorMessage('password')"
          :error-message="getErrorMessage('password')"
          lazy-rules
          hide-bottom-space
          :type="showPassword ? 'text' : 'password'"
        >
          <template v-slot:append>
            <q-btn dense flat round @click="showPassword = !showPassword"
              ><q-icon :name="showPassword ? 'key_off' : 'key'"
            /></q-btn>
          </template>
        </q-input>
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

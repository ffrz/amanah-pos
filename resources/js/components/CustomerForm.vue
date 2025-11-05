<script setup>
import { ref } from "vue";
import { createOptions } from "@/helpers/options";
import StandardCheckBox from "@/components/StandardCheckBox.vue";

const props = defineProps({
  dialogMode: {
    type: Boolean,
    default: false,
  },
  form: {
    type: Object,
    required: true,
  },
  simpleMode: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["submit", "validationError", "update:simpleMode"]);
const types = createOptions(window.CONSTANTS.CUSTOMER_TYPES);
const priceOptions = createOptions(window.CONSTANTS.PRODUCT_PRICE_TYPES);
const showPassword = ref(!props.form.id || props.form.password ? true : false);
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
    <input type="hidden" name="id" v-model="form.id" />

    <q-card square flat class="col" :bordered="!dialogMode">
      <q-card-section :class="dialogMode ? 'q-pa-none' : 'q-pt-none'">
        <q-input
          autofocus
          v-model.trim="form.code"
          label="Kode Pelanggan"
          :error="!!form.errors.code"
          :disable="form.processing"
          :error-message="form.errors.code"
          :rules="[(val) => (val && val.length > 0) || 'Kode harus diisi.']"
          lazy-rules
          hide-bottom-space
          class="q-mb-md"
        />

        <q-input
          v-model.trim="form.name"
          label="Nama Pelanggan"
          :error="!!form.errors.name"
          :disable="form.processing"
          :error-message="form.errors.name"
          :rules="[(val) => (val && val.length > 0) || 'Nama harus diisi.']"
          lazy-rules
          hide-bottom-space
          class="q-mb-md"
        />

        <q-select
          v-model="form.default_price_type"
          :options="priceOptions"
          label="Tipe Harga"
          map-options
          emit-value
          hide-bottom-space
          :error="!!form.errors.default_price_type"
          :error-message="form.errors.default_price_type"
          :disable="form.processing"
          transition-show="jump-up"
          transition-hide="jump-up"
          class="q-mb-md"
        />

        <q-select
          v-if="!simpleMode"
          v-model="form.type"
          label="Jenis Akun"
          :options="types"
          map-options
          emit-value
          lazy-rules
          :disable="form.processing"
          transition-show="jump-up"
          transition-hide="jump-up"
          :error="!!form.errors.type"
          :error-message="form.errors.type"
          hide-bottom-space
          class="q-mb-md"
        />

        <q-input
          v-model.trim="form.phone"
          type="text"
          label="No HP"
          :disable="form.processing"
          :error="!!form.errors.phone"
          :error-message="form.errors.phone"
          lazy-rules
          hide-bottom-space
          class="q-mb-md"
        />

        <q-input
          v-model.trim="form.address"
          type="textarea"
          maxlength="200"
          label="Alamat"
          :disable="form.processing"
          :error="!!form.errors.address"
          :error-message="form.errors.address"
          autogrow
          counter
          lazy-rules
          hide-bottom-space
          class="q-mb-md"
        />

        <StandardCheckBox
          v-model="form.active"
          label="Aktif"
          :disable="form.processing"
          class="q-mb-md"
        />

        <q-input
          v-if="!simpleMode"
          autocomplete="off"
          aria-autocomplete="none"
          v-model="form.password"
          :label="
            !form.id
              ? 'Kata Sandi Baru (Wajib diisi)'
              : 'Kata Sandi (Isi jika ingin mengganti)'
          "
          :disable="form.processing"
          :error="!!form.errors.password"
          :error-message="form.errors.password"
          lazy-rules
          hide-bottom-space
          :type="showPassword ? 'text' : 'password'"
          class="q-mb-md"
        >
          <template v-slot:append>
            <q-btn dense flat round @click="showPassword = !showPassword">
              <q-icon :name="showPassword ? 'key_off' : 'key'" />
            </q-btn>
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

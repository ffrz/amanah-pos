<script setup>
import { ref } from "vue";
import { createOptions } from "@/helpers/options";
import StandardCheckBox from "@/components/StandardCheckBox.vue"; // Assume this component exists

const props = defineProps({
  bordered: {
    type: Boolean,
    default: true,
  },

  // Menerima objek form dari parent (bisa dari Inertia useForm atau ref biasa)
  formData: {
    type: Object,
    required: true,
  },
  // Menerima status loading dari parent
  processing: {
    type: Boolean,
    default: false,
  },
  // Menerima error dari parent (penting untuk Inertia)
  formErrors: {
    type: Object,
    default: () => ({}),
  },
  // Mode Sederhana/Lengkap
  simpleMode: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["submit", "update:simpleMode"]);

// Constants (diasumsikan tersedia di window)
const types = createOptions(window.CONSTANTS.CUSTOMER_TYPES);
const priceOptions = createOptions(window.CONSTANTS.PRODUCT_PRICE_TYPES);

// State lokal untuk menampilkan/menyembunyikan password
const showPassword = ref(!props.formData.id ? true : false);

// Toggle simple mode, menggunakan emit untuk mengupdate prop jika di luar form
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
    <q-card square flat class="col" :bordered="bordered">
      <q-card-section :class="!bordered ? 'q-pa-none' : 'q-pt-none'">
        <!-- Kode Pelanggan -->
        <q-input
          v-model.trim="props.formData.code"
          label="Kode Pelanggan"
          :error="!!props.formErrors.code"
          :disable="props.processing"
          :error-message="props.formErrors.code"
          :rules="[(val) => (val && val.length > 0) || 'Kode harus diisi.']"
          autofocus
          lazy-rules
          hide-bottom-space
        />
        <!-- Nama Pelanggan -->
        <q-input
          v-model.trim="props.formData.name"
          label="Nama Pelanggan"
          :error="!!props.formErrors.name"
          :disable="props.processing"
          :error-message="props.formErrors.name"
          :rules="[(val) => (val && val.length > 0) || 'Nama harus diisi.']"
          lazy-rules
          hide-bottom-space
        />
        <!-- Tipe Harga -->
        <q-select
          v-model="props.formData.default_price_type"
          :options="priceOptions"
          label="Tipe Harga"
          map-options
          emit-value
          hide-bottom-space
          :error="!!props.formErrors.default_price_type"
          :error-message="props.formErrors.default_price_type"
          :disable="props.processing"
          transition-show="jump-up"
          transition-hide="jump-up"
        />
        <!-- Jenis Akun (Hanya mode lengkap) -->
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
          :error="!!props.formErrors.type"
          :error-message="props.formErrors.type"
          hide-bottom-space
        />
        <!-- No HP -->
        <q-input
          v-model.trim="props.formData.phone"
          type="text"
          label="No HP"
          :disable="props.processing"
          :error="!!props.formErrors.phone"
          :error-message="props.formErrors.phone"
          lazy-rules
          hide-bottom-space
        />
        <!-- Alamat -->
        <q-input
          v-model.trim="props.formData.address"
          type="textarea"
          maxlength="200"
          label="Alamat"
          :disable="props.processing"
          :error="!!props.formErrors.address"
          :error-message="props.formErrors.address"
          autogrow
          counter
          lazy-rules
          hide-bottom-space
        />
        <!-- Status Aktif -->
        <StandardCheckBox
          v-model="props.formData.active"
          label="Aktif"
          :disable="props.processing"
        />

        <!-- Password (Hanya mode lengkap) -->
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
          :error="!!props.formErrors.password"
          :error-message="props.formErrors.password"
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

        <!-- Tombol Simple Mode Toggle -->
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

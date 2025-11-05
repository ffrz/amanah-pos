<script setup>
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
    <!-- Akses Data -->
    <input type="hidden" name="id" v-model="form.id" />
    <q-card square flat class="col" :bordered="!dialogMode">
      <q-card-section :class="dialogMode ? 'q-pa-none' : 'q-pt-none'">
        <!-- Kode Pemasok -->

        <q-input
          autofocus
          v-model.trim="form.code"
          label="Kode Pemasok"
          :error="!!form.errors.code"
          :disable="form.processing"
          :error-message="form.errors.code"
          :rules="[(val) => (val && val.length > 0) || 'Kode harus diisi.']"
          lazy-rules
          hide-bottom-space
          class="q-mb-md"
        />

        <!-- Nama Pemasok -->

        <q-input
          v-model.trim="form.name"
          label="Nama Pemasok"
          :error="!!form.errors.name"
          :disable="form.processing"
          :error-message="form.errors.name"
          :rules="[(val) => (val && val.length > 0) || 'Nama harus diisi.']"
          lazy-rules
          hide-bottom-space
          class="q-mb-md"
        />

        <!-- Telepon -->

        <div class="row q-col-gutter-md">
          <q-input
            v-model.trim="form.phone_1"
            type="text"
            label="No Telp"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.phone_1"
            :error-message="form.errors.phone_1"
            hide-bottom-space
            class="col"
          />

          <q-input
            v-if="!simpleMode"
            v-model.trim="form.phone_2"
            type="text"
            label="No Telp 2"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.phone_2"
            :error-message="form.errors.phone_2"
            hide-bottom-space
            class="col"
          />

          <q-input
            v-if="!simpleMode"
            v-model.trim="form.phone_3"
            type="text"
            label="No Telp 3"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.phone_3"
            :error-message="form.errors.phone_3"
            hide-bottom-space
            class="col"
          />
        </div>

        <!-- Alamat -->

        <q-input
          v-model.trim="form.address"
          type="textarea"
          autogrow
          counter
          maxlength="500"
          label="Alamat"
          lazy-rules
          :disable="form.processing"
          :error="!!form.errors.address"
          :error-message="form.errors.address"
          hide-bottom-space
        />

        <!-- Alamat Retur -->

        <q-input
          v-if="!simpleMode"
          v-model.trim="form.return_address"
          type="textarea"
          autogrow
          counter
          maxlength="500"
          label="Alamat Retur"
          lazy-rules
          :disable="form.processing"
          :error="!!form.errors.return_address"
          :error-message="form.errors.return_address"
          hide-bottom-space
        />

        <!-- Rekening 1 -->

        <div v-if="!simpleMode" class="text-subtitle2 q-mt-md text-grey-8">
          Rekening 1
        </div>

        <div class="row q-col-gutter-md" v-if="!simpleMode">
          <q-input
            v-model.trim="form.bank_account_name_1"
            type="text"
            label="Nama Bank"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.bank_account_name_1"
            :error-message="form.errors.bank_account_name_1"
            hide-bottom-space
            class="col-4"
          />

          <q-input
            v-model.trim="form.bank_account_number_1"
            type="text"
            label="Nomor Rekening"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.bank_account_number_1"
            :error-message="form.errors.bank_account_number_1"
            hide-bottom-space
            class="col-4"
          />

          <q-input
            v-model.trim="form.bank_account_holder_1"
            type="text"
            label="Atas Nama"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.bank_account_holder_1"
            :error-message="form.errors.bank_account_holder_1"
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
            v-model.trim="form.bank_account_name_2"
            type="text"
            label="Nama Bank"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.bank_account_name_2"
            :error-message="form.errors.bank_account_name_2"
            hide-bottom-space
            class="col-4"
          />

          <q-input
            v-model.trim="form.bank_account_number_2"
            type="text"
            label="Nomor Rekening"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.bank_account_number_2"
            :error-message="form.errors.bank_account_number_2"
            hide-bottom-space
            class="col-4"
          />

          <q-input
            v-model.trim="form.bank_account_holder_2"
            type="text"
            label="Atas Nama"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.bank_account_holder_2"
            :error-message="form.errors.bank_account_holder_2"
            hide-bottom-space
            class="col-4"
          />
        </div>

        <!-- URLs -->

        <div class="row q-col-gutter-md" v-if="!simpleMode">
          <q-input
            v-model.trim="form.url_1"
            type="text"
            label="URL 1"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.url_1"
            :error-message="form.errors.url_1"
            hide-bottom-space
            class="col"
          />

          <q-input
            v-model.trim="form.url_2"
            type="text"
            label="URL 2"
            lazy-rules
            :disable="form.processing"
            :error="!!form.errors.url_2"
            :error-message="form.errors.url_2"
            hide-bottom-space
            class="col"
          />
        </div>

        <!-- Checkbox Aktif -->

        <StandardCheckBox
          v-model="form.active"
          label="Aktif"
          :disable="form.processing"
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

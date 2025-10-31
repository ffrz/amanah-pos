<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import StandardCheckBox from "@/components/StandardCheckBox.vue";
import { createOptions } from "@/helpers/options";
import { ref } from "vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Pelanggan";
const form = useForm({
  id: page.props.data.id,
  code: page.props.data.code,
  name: page.props.data.name,
  default_price_type: page.props.data.default_price_type,
  phone: page.props.data.phone,
  address: page.props.data.address,
  active: !!page.props.data.active,
  password: !page.props.data.id ? "12345" : null,
  type: page.props.data.type,
});
const showPassword = ref(!page.props.data.id ? true : false);

const types = createOptions(window.CONSTANTS.CUSTOMER_TYPES);

const priceOptions = createOptions(window.CONSTANTS.PRODUCT_PRICE_TYPES);

const submit = () =>
  handleSubmit({
    form,
    url: route("admin.customer.save", {
      id: form.id,
    }),
  });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.customer.index'))"
        />
      </div>
    </template>
    <template #right-button>
      <q-btn
        class="q-ml-xs"
        type="submit"
        icon="check"
        rounded
        dense
        color="primary"
        :disable="form.processing"
        @click="submit()"
        title="Simpan"
      />
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <input type="hidden" name="id" v-model="form.id" />
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <q-input
                v-model.trim="form.code"
                label="Kode Pelanggan"
                :error="!!form.errors.code"
                :disable="form.processing"
                :error-message="form.errors.code"
                :rules="[
                  (val) => (val && val.length > 0) || 'Kode harus diisi.',
                ]"
                autofocus
                lazy-rules
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.name"
                label="Nama Pelanggan"
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                lazy-rules
                hide-bottom-space
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
              />
              <q-select
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
              />
              <q-input
                v-model.trim="form.phone"
                type="text"
                label="No HP"
                :disable="form.processing"
                :error="!!form.errors.phone"
                :error-message="form.errors.phone"
                :rules="[]"
                lazy-rules
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.address"
                type="textarea"
                maxlength="200"
                label="Alamat"
                :disable="form.processing"
                :error="!!form.errors.address"
                :error-message="form.errors.address"
                :rules="[]"
                autogrow
                counter
                lazy-rules
                hide-bottom-space
              />
              <q-input
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
              >
                <template v-slot:append>
                  <q-btn dense flat round @click="showPassword = !showPassword"
                    ><q-icon :name="showPassword ? 'key_off' : 'key'"
                  /></q-btn>
                </template>
              </q-input>
              <StandardCheckBox
                v-model="form.active"
                label="Aktif"
                :disable="form.processing"
              />
            </q-card-section>
          </q-card>
          <input type="submit" style="display: none" />
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

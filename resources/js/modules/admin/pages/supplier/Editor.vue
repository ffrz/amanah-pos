<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { ref } from "vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Pemasok";
const form = useForm({
  id: page.props.data.id,
  code: page.props.data.code,
  name: page.props.data.name,
  phone_1: page.props.data.phone_1,
  phone_2: page.props.data.phone_2,
  phone_3: page.props.data.phone_3,
  url_1: page.props.data.url_1,
  url_2: page.props.data.url_2,
  bank_account_name_1: page.props.data.bank_account_name_1,
  bank_account_holder_1: page.props.data.bank_account_holder_1,
  bank_account_number_1: page.props.data.bank_account_number_1,
  bank_account_name_2: page.props.data.bank_account_name_2,
  bank_account_holder_2: page.props.data.bank_account_holder_2,
  bank_account_number_2: page.props.data.bank_account_number_2,
  address: page.props.data.address,
  return_address: page.props.data.return_address,
  active: !!page.props.data.active,
});

const simpleMode = ref(true);

const submit = () => handleSubmit({ form, url: route("admin.supplier.save") });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>

    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$goBack()"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
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
                label="Nama"
                lazy-rules
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                hide-bottom-space
              />
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
              <div
                v-if="!simpleMode"
                class="text-subtitle2 q-mt-md text-grey-8"
              >
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
                  class="col-3"
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
                  class="col-3"
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
                  class="col-6"
                />
              </div>
              <div
                v-if="!simpleMode"
                class="text-subtitle2 q-mt-md text-grey-8"
              >
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
                  class="col-3"
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
                  class="col-3"
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
                  class="col-6"
                />
              </div>

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

              <div style="margin-left: -10px" class="q-mt-md">
                <q-checkbox
                  class="full-width q-pl-none"
                  v-model="form.active"
                  :disable="form.processing"
                  label="Aktif"
                />
              </div>

              <div class="q-mt-sm">
                <div
                  class="cursor-pointer text-grey-8"
                  clickable
                  @click.stop="simpleMode = !simpleMode"
                >
                  <q-icon
                    :name="simpleMode ? 'stat_minus_1' : 'stat_1'"
                    class="inline-icon"
                  />
                  {{ simpleMode ? "Lebih lengkap" : "Lebih ringkas" }}
                </div>
              </div>
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="$goBack()"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

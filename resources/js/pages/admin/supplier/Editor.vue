<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Pemasok";
const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  phone: page.props.data.phone,
  address: page.props.data.address,
  bank_account_number: page.props.data.bank_account_number,
  return_address: page.props.data.return_address,
  active: !!page.props.data.active,
});

const submit = () => handleSubmit({ form, url: route("admin.supplier.save") });
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
          @click="router.get(route('admin.supplier.index'))"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1">Info Supplier</div>
            </q-card-section>
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input
                autofocus
                v-model.trim="form.name"
                label="Nama"
                lazy-rules
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
              />
              <q-input
                v-model.trim="form.phone"
                type="text"
                label="No HP"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.phone"
                :error-message="form.errors.phone"
              />
              <q-input
                v-model.trim="form.address"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Alamat"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.address"
                :error-message="form.errors.address"
              />
              <q-input
                v-model.trim="form.bank_account_number"
                type="text"
                label="No Rek"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.bank_account_number"
                :error-message="form.errors.bank_account_number"
              />
              <q-input
                v-model.trim="form.return_address"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Alamat Return"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.return_address"
                :error-message="form.errors.return_address"
              />
              <div style="margin-left: -10px">
                <q-checkbox
                  class="full-width q-pl-none"
                  v-model="form.active"
                  :disable="form.processing"
                  label="Aktif"
                />
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
                @click="router.get(route('admin.supplier.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

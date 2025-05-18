<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Santri";
const form = useForm({
  id: page.props.data.id,
  nis: page.props.data.nis,
  name: page.props.data.name,
  parent_name: page.props.data.parent_name,
  phone: page.props.data.phone,
  address: page.props.data.address,
  active: !!page.props.data.active,
  password: page.props.data.password,
});

const submit = () =>
  handleSubmit({ form, url: route('admin.customer.save') });

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1">Info Santri</div>
            </q-card-section>
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input autofocus v-model.trim="form.nis" label="NIS" lazy-rules :error="!!form.errors.nis"
                :disable="form.processing" :error-message="form.errors.nis" :rules="[
                  (val) => (val && val.length > 0) || 'NIS harus diisi.',
                ]" />
              <q-input v-model.trim="form.name" label="Nama Santri" lazy-rules :error="!!form.errors.name"
                :disable="form.processing" :error-message="form.errors.name" :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]" />
              <q-input v-model.trim="form.parent_name" label="Nama Wali Santri" lazy-rules
                :error="!!form.errors.parent_name" :disable="form.processing" :error-message="form.errors.name" :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]" />
              <q-input v-model.trim="form.phone" type="text" label="No HP" lazy-rules :disable="form.processing"
                :error="!!form.errors.phone" :error-message="form.errors.phone" :rules="[
                  (val) => (val && val.length > 0) || 'No HP harus diisi.',
                ]" />
              <q-input v-model.trim="form.address" type="textarea" autogrow counter maxlength="1000" label="Alamat"
                lazy-rules :disable="form.processing" :error="!!form.errors.address"
                :error-message="form.errors.address" :rules="[
                  (val) => (val && val.length > 0) || 'Alamat harus diisi.',
                ]" />
              <q-input v-model="form.password" type="password" label="Kata Sandi (Isi untuk mengatur ulang kata sandi)" lazy-rules :disable="form.processing"
                :error="!!form.errors.password" :error-message="form.errors.password" />
              <div style="margin-left: -10px;">
                <q-checkbox class="full-width q-pl-none" v-model="form.active" :disable="form.processing"
                  label="Aktif" />
              </div>
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing"
                @click="router.get(route('admin.customer.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>

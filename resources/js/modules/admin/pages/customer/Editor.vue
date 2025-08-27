<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import StandardCheckBox from "@/components/StandardCheckBox.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Pelanggan";
const form = useForm({
  id: page.props.data.id,
  username: page.props.data.username,
  name: page.props.data.name,
  phone: page.props.data.phone,
  address: page.props.data.address,
  active: !!page.props.data.active,
  password: page.props.data.password,
});

const submit = () => handleSubmit({ form, url: route("admin.customer.save") });
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
          @click="$inertia.get(route('admin.customer.index'))"
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
          <input type="hidden" name="id" v-model="form.id" />
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <q-input
                v-model.trim="form.username"
                label="NIS"
                :error="!!form.errors.username"
                :disable="form.processing"
                :error-message="form.errors.username"
                :rules="[
                  (val) => (val && val.length > 0) || 'NIS harus diisi.',
                ]"
                autofocus
                lazy-rules
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.name"
                label="Nama Santri"
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                lazy-rules
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.phone"
                type="text"
                label="No HP"
                :disable="form.processing"
                :error="!!form.errors.phone"
                :error-message="form.errors.phone"
                :rules="[
                  (val) => (val && val.length > 0) || 'No HP harus diisi.',
                ]"
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
                :rules="[
                  (val) => (val && val.length > 0) || 'Alamat harus diisi.',
                ]"
                autogrow
                counter
                lazy-rules
                hide-bottom-space
              />
              <q-input
                v-model="form.password"
                type="password"
                :label="
                  form.id
                    ? 'Kata Sandi Baru (Wajib diisi)'
                    : 'Kata Sandi (Isi jika ingin mengganti)'
                "
                :disable="form.processing"
                :error="!!form.errors.password"
                :error-message="form.errors.password"
                lazy-rules
                hide-bottom-space
              />
              <StandardCheckBox
                v-model="form.active"
                label="Aktif"
                :disable="form.processing"
              />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                type="submit"
                label="Simpan"
                icon="save"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                label="Batal"
                icon="cancel"
                :disable="form.processing"
                @click="router.get(route('admin.customer.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

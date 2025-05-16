<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Layanan";
const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  duration: page.props.data.duration,
  price: page.props.data.price,
  description: page.props.data.description,
  active: !!page.props.data.active,
});

const submit = () =>
  handleSubmit({form, url: route('admin.wash-service.save')});

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
              <div class="text-subtitle1">Info Layanan</div>
            </q-card-section>
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input autofocus v-model.trim="form.name" label="Nama" lazy-rules :error="!!form.errors.name"
                :disable="form.processing" :error-message="form.errors.name" :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]" />
              <q-input v-model.trim="form.description" label="Deskripsi" lazy-rules :error="!!form.errors.name"
                :disable="form.processing" :error-message="form.errors.description" :rules="[
                  (val) => (val && val.length > 0) || 'Deskripsi harus diisi.',
                ]" />
              <LocaleNumberInput v-model:modelValue="form.price" label="Harga / Biaya (Rp)" lazyRules
                :disable="form.processing" :error="!!form.errors.price"
                :errorMessage="form.errors.price" :rules="[]" />
              <LocaleNumberInput v-model:modelValue="form.duration" label="Perkiraan Durasi (Menit)" lazyRules
                :disable="form.processing" :error="!!form.errors.duration"
                :errorMessage="form.errors.duration" :rules="[]" />
              <div style="margin-left: -10px;">
                <q-checkbox class="full-width q-pl-none" v-model="form.active" :disable="form.processing"
                  label="Aktif" />
              </div>
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing" @click="router.get(route('admin.wash-service.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>

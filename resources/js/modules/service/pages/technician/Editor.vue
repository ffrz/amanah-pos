<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import StandardCheckBox from "@/components/StandardCheckBox.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Teknisi Service";

/**
 * Inisialisasi Form Inertia
 * Menyesuaikan field dengan fillable model ServiceTechnician
 */
const form = useForm({
  id: page.props.data.id ?? null,
  user_id: page.props.data.user_id ?? null,
  code: page.props.data.code ?? "",
  name: page.props.data.name ?? "",
  email: page.props.data.email ?? "",
  phone: page.props.data.phone ?? "",
  address: page.props.data.address ?? "",
  active: page.props.data.id ? !!page.props.data.active : true,
});

const submit = () =>
  handleSubmit({
    form,
    url: route("service.technician.save", {
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
          @click="router.get(route('service.technician.index'))"
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
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <input type="hidden" name="id" v-model="form.id" />

          <q-card square flat bordered>
            <q-card-section>
              <q-input
                autofocus
                v-model.trim="form.code"
                label="Kode"
                :error="!!form.errors.code"
                :disable="form.processing"
                :error-message="form.errors.code"
                :rules="[
                  (val) => (val && val.length > 0) || 'Kode harus diisi.',
                ]"
                lazy-rules
                hide-bottom-space
                class="q-mb-md"
              />

              <q-input
                v-model.trim="form.name"
                label="Nama Lengkap"
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                lazy-rules
                hide-bottom-space
                class="q-mb-md"
              />

              <q-input
                v-model.trim="form.email"
                type="email"
                label="Email"
                placeholder="contoh@email.com"
                :error="!!form.errors.email"
                :disable="form.processing"
                :error-message="form.errors.email"
                lazy-rules
                hide-bottom-space
                class="q-mb-md"
              />

              <q-input
                v-model.trim="form.phone"
                type="tel"
                label="Nomor Telepon / WA"
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
                maxlength="255"
                label="Alamat Lengkap"
                :disable="form.processing"
                :error="!!form.errors.address"
                :error-message="form.errors.address"
                autogrow
                counter
                lazy-rules
                hide-bottom-space
                class="q-mb-md"
              />

              <div class="bg-grey-1 q-pa-sm rounded-borders">
                <StandardCheckBox
                  v-model="form.active"
                  label="Aktif"
                  :disable="form.processing"
                />
                <div class="text-caption text-grey-7 q-ml-md">
                  Teknisi aktif akan muncul dalam pilihan penugasan order
                  service.
                </div>
              </div>
            </q-card-section>
          </q-card>

          <input type="submit" style="display: none" />
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

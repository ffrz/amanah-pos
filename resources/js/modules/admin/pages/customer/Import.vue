<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { scrollToFirstErrorField } from "@/helpers/utils";

const title = "Import Pelanggan";

// Membuat form khusus untuk file upload
const form = useForm({
  csv_file: null,
});

const submit = () => {
  // Menggunakan Inertia.js untuk mengirim form dengan file
  // Pastikan URL-nya mengarah ke controller import Anda
  form.post(route("admin.customer.import"), {
    onSuccess: () => {
      // Beri notifikasi sukses
      // Opsi: reset form setelah sukses
      form.reset();
      // Opsi: navigasi ke halaman lain
      // router.visit(route('admin.product.index'));
    },
    onError: () => {
      // Gulir ke field error pertama
      scrollToFirstErrorField();
    },
    // Opsi: tampilkan progress bar
    onFinish: () => {
      form.clearErrors();
    },
  });
};
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
          @click="router.get(route('admin.customer.index'))"
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
              <div class="text-subtitle1 q-pt-lg">Unggah File Impor</div>
              <div class="text-caption text-grey">
                Unggah file CSV untuk mengimpor data pelanggan. Pastikan format
                kolom sesuai dengan template yang disediakan.
              </div>
              <q-file
                v-model="form.csv_file"
                accept=".csv"
                label="Pilih file CSV"
                :error="!!form.errors.csv_file"
                :error-message="form.errors.csv_file"
                :disable="form.processing"
                hide-bottom-space
                class="q-mt-md"
              />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="upload"
                type="submit"
                label="Import Data"
                color="primary"
                :disable="form.processing || !form.csv_file"
                :loading="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="router.get(route('admin.customer.index'))"
              />
            </q-card-section>
            <q-banner
              v-if="$page.props.flash.success"
              class="bg-positive text-white q-mt-md"
            >
              {{ $page.props.flash.success }}
            </q-banner>
            <q-banner
              v-if="$page.props.flash.error"
              class="bg-negative text-white q-mt-md"
            >
              {{ $page.props.flash.error }}
            </q-banner>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

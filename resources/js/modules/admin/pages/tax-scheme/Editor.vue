<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useQuasar } from "quasar";

const $q = useQuasar();
const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Skema Pajak";

const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  rate_percentage: page.props.data.rate_percentage ?? 0.0,
  tax_authority: page.props.data.tax_authority,
  is_inclusive: page.props.data.is_inclusive ?? false,
  active: page.props.data.active ?? true,
  description: page.props.data.description,
});

const submit = () =>
  handleSubmit({
    form,
    url: route("admin.tax-scheme.save"),
  });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="!$q.screen.lt.md">
    <template #title>{{ title }}</template>

    <template #left-button v-if="$q.screen.lt.md">
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.tax-scheme.index'))"
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
                autofocus
                v-model.trim="form.name"
                label="Nama Skema Pajak"
                lazy-rules
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                hide-bottom-space
              />

              <!-- Input Persentase Tarif Pajak -->
              <q-input
                v-model.number="form.rate_percentage"
                label="Tarif Persentase (%)"
                type="number"
                suffix="%"
                :rules="[
                  (val) => val >= 0 || 'Persentase tidak boleh negatif.',
                ]"
                :error="!!form.errors.rate_percentage"
                :disable="form.processing"
                :error-message="form.errors.rate_percentage"
                hide-bottom-space
              />

              <!-- Input Otoritas Pajak -->
              <q-input
                v-model.trim="form.tax_authority"
                label="Otoritas Pajak (Opsional)"
                hint="Contoh: DJP, Pemerintah Daerah"
                :error="!!form.errors.tax_authority"
                :disable="form.processing"
                :error-message="form.errors.tax_authority"
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.description"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Deskripsi"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.description"
                :error-message="form.errors.description"
                hide-bottom-space
              />
              <div class="q-pt-md">
                <q-toggle
                  v-model="form.is_inclusive"
                  :disable="form.processing"
                  :label="
                    form.is_inclusive
                      ? 'Harga Jual SUDAH termasuk Pajak'
                      : 'Harga Jual BELUM termasuk Pajak'
                  "
                  color="primary"
                />
                <div
                  class="text-caption text-negative"
                  v-if="form.errors.is_inclusive"
                >
                  {{ form.errors.is_inclusive }}
                </div>
              </div>
              <div class="q-pt-md">
                <q-toggle
                  v-model="form.active"
                  :disable="form.processing"
                  :label="form.active ? 'Skema Aktif' : 'Skema Non-Aktif'"
                  color="primary"
                />
                <div
                  class="text-caption text-negative"
                  v-if="form.errors.active"
                >
                  {{ form.errors.active }}
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
                :loading="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="router.get(route('admin.tax-scheme.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

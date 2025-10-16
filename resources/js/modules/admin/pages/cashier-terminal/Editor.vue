<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import StandardCheckBox from "@/components/StandardCheckBox.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Terminal";
const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  finance_account_id: page.props.data.finance_account_id ?? "new",
  location: page.props.data.location,
  active: !!page.props.data.active,
  notes: page.props.data.notes,
});

const accounts = [
  {
    value: "new",
    label: "Buat Baru (Otomatis)",
  },
  ...page.props.finance_accounts.map((item) => {
    return {
      value: item.id,
      label: item.name,
    };
  }),
];

const submit = () =>
  handleSubmit({ form, url: route("admin.cashier-terminal.save") });
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
          @click="$inertia.get(route('admin.cashier-terminal.index'))"
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
          <input type="hidden" name="id" v-model="form.id" />
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <q-input
                v-model.trim="form.name"
                label="Nama"
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                autofocus
                lazy-rules
                hide-bottom-space
              />
              <q-select
                class="custom-select"
                v-model="form.finance_account_id"
                label="Akun Kas"
                :options="accounts"
                map-options
                emit-value
                :errorMessage="form.errors.finance_account_id"
                :error="!!form.errors.finance_account_id"
                :disable="form.processing"
                hide-bottom-space
              />
              <div
                v-if="form.finance_account_id === 'new'"
                class="q-my-xs text-grey-8"
              >
                Akun keuangan akan dibuat
                <template v-if="form.name">
                  dengan nama Kas {{ form.name }}
                </template>
              </div>
              <q-input
                v-model.trim="form.location"
                label="Lokasi"
                :error="!!form.errors.location"
                :disable="form.processing"
                :error-message="form.errors.location"
                :rules="[]"
                lazy-rules
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                maxlength="200"
                label="Catatan"
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                :rules="[]"
                autogrow
                counter
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
                @click="router.get(route('admin.cashier-terminal.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

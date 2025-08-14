<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { createOptions } from "@/helpers/options";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Akun / Kas";
const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  type: page.props.data.type,
  bank: page.props.data.bank,
  number: page.props.data.number,
  holder: page.props.data.holder,
  balance: page.props.data.balance,
  active: !!page.props.data.active,
  notes: page.props.data.notes,
});

const types = createOptions(window.CONSTANTS.FINANCE_ACCOUNT_TYPES);

const submit = () =>
  handleSubmit({ form, url: route("admin.finance-account.save") });
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
          @click="$inertia.get(route('admin.finance-account.index'))"
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
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input
                autofocus
                v-model.trim="form.name"
                label="Nama Akun"
                lazy-rules
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
              />
              <q-select
                v-model="form.type"
                label="Jenis Akun"
                :options="types"
                map-options
                emit-value
                :error="!!form.errors.type"
                :disable="form.processing"
              >
              </q-select>
              <template v-if="form.type == 'bank'">
                <q-input
                  v-model.trim="form.bank"
                  label="Nama Bank"
                  lazy-rules
                  :error="!!form.errors.bank"
                  :disable="form.processing"
                  :error-message="form.errors.bank"
                />
                <q-input
                  v-model.trim="form.number"
                  label="Nomor Rekening"
                  lazy-rules
                  :error="!!form.errors.number"
                  :disable="form.processing"
                  :error-message="form.errors.number"
                />
                <q-input
                  v-model.trim="form.holder"
                  label="Pemilik Rekening"
                  lazy-rules
                  :error="!!form.errors.holder"
                  :disable="form.processing"
                  :error-message="form.errors.holder"
                />
              </template>
              <LocaleNumberInput
                v-model:modelValue="form.balance"
                label="Saldo"
                lazyRules
                :disable="form.processing"
                allow-negative="true"
                :error="!!form.errors.balance"
                :errorMessage="form.errors.balance"
                :rules="[]"
              />
              <div style="margin-left: -10px">
                <q-checkbox
                  class="full-width q-pl-none"
                  v-model="form.active"
                  :disable="form.processing"
                  label="Aktif"
                />
              </div>
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="255"
                label="Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
              />
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
                @click="router.get(route('admin.finance-account.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

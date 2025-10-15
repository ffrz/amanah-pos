<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";

const page = usePage();
const title = "Tambah Retur Pembelian";
const form = useForm({
  code: page.props.data.code,
});

const submit = () =>
  handleSubmit({ form, url: route("admin.purchase-order-return.add") });
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
      <div class="col col-md-6 q-pa-xs">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <q-input
                autofocus
                v-model.trim="form.code"
                label="Kode Transaksi"
                lazy-rules
                :error="!!form.errors.code"
                :disable="form.processing"
                :error-message="form.errors.code"
                :rules="[
                  (val) => (val && val.length > 0) || 'Kode Order harus diisi.',
                ]"
                hide-bottom-space
              />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="send"
                type="submit"
                label="Lanjutkan"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="router.get(route('admin.purchase-order-return.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

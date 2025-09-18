<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit, transformPayload } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import ImageUpload from "@/components/ImageUpload.vue";
const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Catat") + " Transaksi Keuangan";

const accounts = page.props.accounts.map((account) => ({
  label: account.name,
  value: account.id,
}));

const types = [
  { label: "Pemasukan (+)", value: "income" },
  { label: "Pengeluaran (-)", value: "expense" },
  { label: "Transfer", value: "transfer" },
];

const form = useForm({
  id: page.props.data.id,
  account_id: page.props.data.account_id,
  to_account_id: page.props.data.to_account_id ?? null,
  type: page.props.data.type,
  datetime: new Date(page.props.data.datetime),
  notes: page.props.data.notes,
  amount: parseFloat(page.props.data.amount),
  image_path: page.props.data?.image_path ?? "",
  image: null,
});

const submit = () => {
  transformPayload(form, { datetime: "YYYY-MM-DD HH:mm:ss" });
  handleSubmit({ form, url: route("admin.finance-transaction.save") });
};
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
          @click="$inertia.get(route('admin.finance-transaction.index'))"
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
              <date-time-picker
                v-model="form.datetime"
                label="Waktu"
                :error="!!form.errors.datetime"
                :disable="form.processing"
                :errorMessage="form.errors.datetime"
                hide-bottom-space
              />
              <q-select
                autofocus
                v-model="form.type"
                label="Jenis Transaksi"
                :options="types"
                map-options
                emit-value
                :error="!!form.errors.type"
                :disable="form.processing"
                :errorMessage="form.errors.type"
                hide-bottom-space
              >
              </q-select>
              <q-select
                class="custom-select"
                v-model="form.account_id"
                :label="form.type == 'transfer' ? 'Akun Asal' : 'Akun'"
                :options="accounts"
                map-options
                emit-value
                :errorMessage="form.errors.account_id"
                :error="!!form.errors.account_id"
                :disable="form.processing"
                hide-bottom-space
              />
              <q-select
                v-if="form.type == 'transfer'"
                class="custom-select"
                v-model="form.to_account_id"
                label="Akun Tujuan"
                :options="accounts"
                map-options
                emit-value
                :errorMessage="form.errors.to_account_id"
                :error="!!form.errors.to_account_id"
                :disable="form.processing"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.amount"
                label="Jumlah"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.amount"
                :errorMessage="form.errors.amount"
                :rules="[]"
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                :rules="[
                  (val) => (val && val.length > 0) || 'Catatan harus diisi.',
                ]"
                hide-bottom-space
              />
              <ImageUpload
                v-model="form.image"
                :initial-image-path="form.image_path"
                :disabled="form.processing"
                :error="!!form.errors.image || !!form.errors.image_path"
                :error-message="form.errors.image || form.errors.image_path"
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
                @click="router.get(route('admin.finance-transaction.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

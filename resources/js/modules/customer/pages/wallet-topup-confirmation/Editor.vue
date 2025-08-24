<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import { formatDateTime, formatDateTimeForEditing } from "@/helpers/formatter";
import { useFinanceAccount } from "@/composables/useFinanceAccount";

const page = usePage();
const title = " Konfirmasi Topup";
const { accountOptions } = useFinanceAccount(page.props.accounts);

const form = useForm({
  username: page.props.auth.customer.username,
  name: page.props.auth.customer.name,
  finance_account_id: null,
  datetime: formatDateTimeForEditing(new Date()),
  amount: 0,
  notes: "",
});

const submit = () =>
  handleSubmit({ form, url: route("customer.wallet-topup-confirmation.save") });
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
          @click="
            $inertia.get(route('customer.wallet-topup-confirmation.index'))
          "
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
                readonly
                v-model.trim="form.username"
                label="NIS"
                :disable="form.processing"
                hide-bottom-space
              />

              <q-input
                readonly
                v-model.trim="form.name"
                label="Nama"
                :disable="form.processing"
                hide-bottom-space
              />

              <q-select
                class="custom-select"
                v-model="form.finance_account_id"
                label="Akun Tujuan"
                :options="accountOptions"
                map-options
                emit-value
                :errorMessage="form.errors.finance_account_id"
                :error="!!form.errors.finance_account_id"
                :disable="form.processing"
                hide-bottom-space
              />

              <date-time-picker
                v-model="form.datetime"
                label="Tanggal & Waktu Transfer"
                :error="!!form.errors.datetime"
                :disable="form.processing"
                hide-bottom-space
              />

              <LocaleNumberInput
                v-model:modelValue="form.amount"
                label="Jumlah (Rp.)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.amount"
                :errorMessage="form.errors.amount"
                :rules="[]"
                hide-bottom-space
              />

              <q-file
                v-model="form.image"
                label="Unggah Bukti Transfer"
                accept=".jpg, image/*"
                :disable="form.processing"
                :error="!!form.errors.image"
                :error-message="form.errors.image"
                clearable
                hide-bottom-space
              >
                <template v-slot:prepend>
                  <q-icon name="attach_file" />
                </template>
              </q-file>

              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="100"
                label="Catatan (Opsional)"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                hide-bottom-space
              />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan Konfirmasi"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="
                  router.get(route('customer.wallet-topup-confirmation.index'))
                "
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

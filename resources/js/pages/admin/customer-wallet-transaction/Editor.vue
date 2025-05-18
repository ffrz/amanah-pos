<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { useCustomerFilter } from '@/helpers/useCustomerFilter';
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import dayjs from "dayjs";
const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Catat") + " Transaksi Dompet Santri";

const { filteredCustomers, filterCustomersFn } = useCustomerFilter(page.props.customers, false);

const types = [
  { label: 'Deposit (+)', value: 'deposit' },
  { label: 'Penarikan (-)', value: 'withdrawal' },
  { label: 'Pembelian (-)', value: 'purchase' },
  { label: 'Refund (+)', value: 'refund' },
];

const finance_accounts = page.props.finance_accounts.map((account) => ({
  label: account.name,
  value: account.id,
}));

const form = useForm({
  id: page.props.data.id,
  customer_id: page.props.data.customer_id,
  finance_account_id: page.props.data.finance_account_id,
  type: page.props.data.type,
  datetime: dayjs(page.props.data.datetime).format('YYYY-MM-DD HH:mm:ss'),
  notes: page.props.data.notes,
  amount: parseFloat(page.props.data.amount),
});

const submit = () => handleSubmit({ form, url: route('admin.customer-wallet-transaction.save') });

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <date-time-picker autofocus v-model="form.datetime" label="Tanggal" :error="!!form.errors.datetime"
                :disable="form.processing" />
              <q-select class="custom-select" v-model="form.customer_id" label="Santri" use-input input-debounce="300"
                clearable :options="filteredCustomers" map-options emit-value :errorMessage="form.errors.customer_id"
                @filter="filterCustomersFn" :error="!!form.errors.customer_id" :disable="form.processing">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Santri tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <q-select v-model="form.type" label="Jenis" :options="types" map-options emit-value
                :error="!!form.errors.type" :disable="form.processing" :errorMessage="form.errors.type">
              </q-select>
              <q-select v-if="form.type == 'deposit' || form.type == 'withdrawal'" v-model="form.finance_account_id"
                :label="form.type == 'deposit' ? 'Kas Tujuan' : 'Kas Asal' "
                :options="finance_accounts" map-options emit-value :error="!!form.errors.finance_account_id"
                :disable="form.processing" :errorMessage="form.errors.finance_account_id">
              </q-select>
              <LocaleNumberInput v-model:modelValue="form.amount" label="Jumlah" lazyRules :disable="form.processing"
                :error="!!form.errors.amount" :errorMessage="form.errors.amount" :rules="[]" />
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="255" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes"
                :rules="[
                  (val) => (val && val.length > 0) || 'Catatan harus diisi.',
                ]" />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing"
                @click="router.get(route('admin.customer-wallet-transaction.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>

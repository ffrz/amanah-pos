<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { watch } from "vue";

const page = usePage();
const title = "Buka Sesi Kasir";
const form = useForm({
  cashier_terminal_id: page.props.data.cashier_terminal_id,
  opening_balance: parseFloat(page.props.data.opening_balance),
  opening_notes: page.props.data.opening_notes,
});

const cashier_terminals = [
  ...page.props.cashier_terminals.map((item) => {
    return {
      value: item.id,
      label: item.name,
      balance: parseFloat(item.finance_account.balance),
    };
  }),
];

const submit = () =>
  handleSubmit({ form, url: route("admin.cashier-session.open") });

watch(
  () => form.cashier_terminal_id,
  (newId) => {
    if (newId) {
      const selectedRegister = cashier_terminals.find(
        (register) => register.value === newId
      );

      if (selectedRegister) {
        form.opening_balance = selectedRegister.balance;
      }
    } else {
      form.opening_balance = 0;
    }
  }
);
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
          @click="$inertia.get(route('admin.cashier-session.index'))"
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
              <q-select
                class="custom-select"
                v-model="form.cashier_terminal_id"
                label="Terminal Kasir"
                :options="cashier_terminals"
                map-options
                emit-value
                :errorMessage="form.errors.cashier_terminal_id"
                :error="!!form.errors.cashier_terminal_id"
                :disable="form.processing"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.opening_balance"
                label="Saldo Buka Sesi"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.opening_balance"
                :errorMessage="form.errors.opening_balance"
                :rules="[]"
                readonly
                hide-bottom-space
              />
              <div class="q-my-sm text-red">
                Pastikan saldo awal sesuai dengan jumlah uang di laci!
              </div>
              <q-input
                v-model.trim="form.opening_notes"
                type="textarea"
                maxlength="200"
                label="Catatan"
                :disable="form.processing"
                :error="!!form.errors.opening_notes"
                :error-message="form.errors.opening_notes"
                :rules="[]"
                autogrow
                counter
                lazy-rules
                hide-bottom-space
              />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                type="submit"
                label="Buka Sesi"
                icon="login"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                label="Batal"
                icon="cancel"
                :disable="form.processing"
                @click="router.get(route('admin.cashier-session.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

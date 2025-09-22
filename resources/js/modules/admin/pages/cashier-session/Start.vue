<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { watch } from "vue";

const page = usePage();
const title = "Mulai Sesi Kasir";
const form = useForm({
  cash_register_id: page.props.data.cash_register_id,
  opening_balance: parseFloat(page.props.data.opening_balance),
});

const cash_registers = [
  ...page.props.cash_registers.map((item) => {
    return {
      value: item.id,
      label: item.name,
      balance: parseFloat(item.finance_account.balance),
    };
  }),
];

const submit = () =>
  handleSubmit({ form, url: route("admin.cashier-session.start") });

watch(
  () => form.cash_register_id,
  (newId) => {
    if (newId) {
      const selectedRegister = cash_registers.find(
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
                v-model="form.cash_register_id"
                label="Cash Register"
                :options="cash_registers"
                map-options
                emit-value
                :errorMessage="form.errors.cash_register_id"
                :error="!!form.errors.cash_register_id"
                :disable="form.processing"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.opening_balance"
                label="Saldo Awal"
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
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                type="submit"
                label="Mulai"
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

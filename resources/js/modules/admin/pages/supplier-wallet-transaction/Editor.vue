<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit, transformPayload } from "@/helpers/client-req-handler";
import { useCustomerFilter } from "@/composables/useCustomerFilter";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { formatMoney } from "@/helpers/formatter"; // [UPDATE] Import formatMoney
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import ImageUpload from "@/components/ImageUpload.vue";
import { computed } from "vue";

const page = usePage();
const title =
  (!!page.props.data.id ? "Edit" : "Catat") + " Transaksi Deposit Suppllier";

const { filteredCustomers, filterCustomersFn } = useCustomerFilter(
  page.props.suppliers,
  false
);

const types = [
  { label: "Deposit / Uang Masuk (+)", value: "deposit" },
  { label: "Pengembalian / Uang Keluar (-)", value: "withdrawal" },
];

const finance_accounts = page.props.finance_accounts.map((account) => ({
  label: account.name,
  value: account.id,
  balance: parseFloat(account.balance),
}));

const form = useForm({
  id: page.props.data.id,
  supplier_id: page.props.data.supplier_id,
  finance_account_id: page.props.data.finance_account_id,
  type: page.props.data.type,
  datetime: new Date(page.props.data.datetime),
  notes: page.props.data.notes,
  amount: parseFloat(page.props.data.amount),
  image_path: page.props.data?.image_path ?? "",
  image: null,
});

// [LOGIKA] Saldo Wallet Supplier (Aset Kita)
// Deposit = Nambah (+), Withdrawal = Kurang (-)
const supplierWalletInfo = computed(() => {
  const supplier = page.props.suppliers.find((s) => s.id === form.supplier_id);
  if (!supplier) return null;

  const currentBalance = parseFloat(supplier.wallet_balance || 0);
  const amount = form.amount || 0;
  let finalBalance = currentBalance;

  if (form.type === "deposit") {
    finalBalance = currentBalance + amount;
  } else if (form.type === "withdrawal") {
    finalBalance = currentBalance - amount;
  }

  return { current: currentBalance, final: finalBalance };
});

// [LOGIKA] Saldo Kas Keuangan (Uang Fisik)
// Deposit = Uang Keluar (-), Withdrawal = Uang Masuk (+)
const accountInfo = computed(() => {
  const account = finance_accounts.find(
    (a) => a.value === form.finance_account_id
  );
  if (!account) return null;

  const currentBalance = account.balance;
  let finalBalance = currentBalance;
  const amount = form.amount || 0;

  if (form.type === "deposit") {
    finalBalance = currentBalance - amount;
  } else if (form.type === "withdrawal") {
    finalBalance = currentBalance + amount;
  }

  return { current: currentBalance, final: finalBalance };
});

const submit = () => {
  transformPayload(form, { datetime: "YYYY-MM-DD HH:mm:ss" });
  handleSubmit({ form, url: route("admin.supplier-wallet-transaction.save") });
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
          @click="
            $inertia.get(route('admin.supplier-wallet-transaction.index'))
          "
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
                autofocus
                v-model="form.datetime"
                label="Tanggal"
                :error="!!form.errors.datetime"
                :disable="form.processing"
                hide-bottom-space
              />

              <q-select
                class="custom-select"
                v-model="form.supplier_id"
                label="Supplier"
                use-input
                input-debounce="300"
                clearable
                :options="filteredCustomers"
                map-options
                emit-value
                :errorMessage="form.errors.supplier_id"
                @filter="filterCustomersFn"
                :error="!!form.errors.supplier_id"
                :disable="form.processing"
                hide-bottom-space
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Supplier tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>

              <div
                v-if="supplierWalletInfo"
                class="q-py-sm"
                style="font-size: 0.85em"
              >
                <div class="row justify-between">
                  <span class="text-grey-7">Deposit Saat Ini:</span>
                  <span class="text-weight-medium">{{
                    formatMoney(supplierWalletInfo.current)
                  }}</span>
                </div>
                <div class="row justify-between">
                  <span class="text-grey-7">Estimasi Akhir:</span>
                  <span
                    class="text-weight-bold"
                    :class="
                      supplierWalletInfo.final < 0
                        ? 'text-negative'
                        : 'text-primary'
                    "
                  >
                    {{ formatMoney(supplierWalletInfo.final) }}
                  </span>
                </div>
              </div>

              <q-select
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
                v-model="form.finance_account_id"
                :label="
                  form.type == 'deposit'
                    ? 'Kas Sumber (Keluar)'
                    : 'Kas Tujuan (Masuk)'
                "
                :options="finance_accounts"
                map-options
                emit-value
                :error="!!form.errors.finance_account_id"
                :disable="form.processing"
                :errorMessage="form.errors.finance_account_id"
                hide-bottom-space
              >
              </q-select>

              <div v-if="accountInfo" class="q-py-sm" style="font-size: 0.85em">
                <div class="row justify-between">
                  <span class="text-grey-7">Saldo Kas Saat Ini:</span>
                  <span class="text-weight-medium">{{
                    formatMoney(accountInfo.current)
                  }}</span>
                </div>
                <div class="row justify-between">
                  <span class="text-grey-7">Estimasi Akhir:</span>
                  <span
                    class="text-weight-bold"
                    :class="
                      accountInfo.final < 0 ? 'text-negative' : 'text-primary'
                    "
                  >
                    {{ formatMoney(accountInfo.final) }}
                  </span>
                </div>
                <div
                  v-if="accountInfo.final < 0"
                  class="text-negative text-caption q-mt-xs"
                >
                  * Saldo kas akan menjadi negatif
                </div>
              </div>

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
                :rules="[]"
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
                @click="
                  $inertia.get(route('admin.supplier-wallet-transaction.index'))
                "
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { useCustomerFilter } from "@/composables/useCustomerFilter";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { computed, watch } from "vue";
const page = usePage();
const title = "Penyesuaian Saldo Dompet Pelanggan";

const { filteredCustomers, filterCustomersFn } = useCustomerFilter(
  page.props.customers,
  false
);

const form = useForm({
  customer_id: null,
  old_balance: 0,
  new_balance: 0,
  notes: "",
});

const updateAmount = () => (form.amount = form.new_balance - form.old_balance);

// Fetch balance saat customer_id berubah
watch(
  () => form.customer_id,
  async (newCustomerId) => {
    if (!newCustomerId) {
      form.old_balance = 0;
      form.new_balance = 0;
      updateAmount();
      return;
    }

    form.processing = true;
    try {
      const res = await axios.get(
        route("admin.customer.balance", { id: newCustomerId })
      );
      form.new_balance = form.old_balance = res.data.balance;
    } catch (err) {
      console.error("Gagal mengambil saldo:", err);
      form.old_balance = 0;
      form.new_balance = 0;
    }
    form.processing = false;
    updateAmount();
  }
);

const amount = computed(() => form.new_balance - form.old_balance);

const submit = () =>
  handleSubmit({
    form,
    url: route("admin.customer-wallet-transaction.adjustment"),
  });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <q-select
                class="custom-select"
                v-model="form.customer_id"
                label="Pelanggan"
                use-input
                input-debounce="300"
                clearable
                :options="filteredCustomers"
                map-options
                emit-value
                :errorMessage="form.errors.customer_id"
                @filter="filterCustomersFn"
                :error="!!form.errors.customer_id"
                :disable="form.processing"
                hide-bottom-space
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Pelanggan tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <LocaleNumberInput
                v-model:modelValue="form.old_balance"
                label="Saldo Tercatat"
                readonly
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.balance"
                :errorMessage="form.errors.balance"
              />
              <LocaleNumberInput
                v-model:modelValue="form.new_balance"
                label="Saldo Seharusnya"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.new_balance"
                :errorMessage="form.errors.new_balance"
              />
              <LocaleNumberInput
                v-model:modelValue="amount"
                label="Selisih"
                readonly
                lazyRules
                :disable="form.processing"
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
                  router.get(route('admin.customer-wallet-transaction.index'))
                "
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

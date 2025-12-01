<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit, transformPayload } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import ImageUpload from "@/components/ImageUpload.vue";
import { computed } from "vue";

const page = usePage();
const title = "Buat Pengajuan Setoran Kas";

// Mapping opsi akun keuangan dari props
const accountOptions = page.props.finance_accounts.map((acc) => ({
  label: acc.name,
  value: acc.id,
  // Optional: tampilkan bank/nomor jika ada
  description: acc.bank ? `${acc.bank} - ${acc.number}` : null,
}));

const form = useForm({
  datetime: new Date(),
  cashier_terminal_id: page.props.data.cashier_terminal_id,
  cashier_session_id: page.props.data.cashier_session_id,
  source_finance_account_id: page.props.data.source_finance_account_id ?? null,
  target_finance_account_id: page.props.data.target_finance_account_id ?? null,
  amount: 0,
  notes: "",
  image: null,
});

// Filter opsi Target agar tidak bisa memilih akun yang sama dengan Source
const targetAccountOptions = computed(() => {
  if (!form.source_finance_account_id) return accountOptions;
  return accountOptions.filter(
    (acc) => acc.value !== form.source_finance_account_id
  );
});

// Filter opsi Source agar tidak bisa memilih akun yang sama dengan Target
const sourceAccountOptions = computed(() => {
  if (!form.target_finance_account_id) return accountOptions;
  return accountOptions.filter(
    (acc) => acc.value !== form.target_finance_account_id
  );
});

const submit = () => {
  transformPayload(form, { datetime: "YYYY-MM-DD HH:mm:ss" });
  handleSubmit({ form, url: route("admin.cashier-cash-drop.save") });
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout ref="layoutRef">
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('admin.cashier-cash-drop.index'))"
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
            <q-card-section>
              <!-- Tanggal -->
              <date-time-picker
                v-model="form.datetime"
                label="Waktu Setoran"
                :error="!!form.errors.datetime"
                disable
                hide-bottom-space
              />

              <!-- Akun Sumber (Dari mana uang diambil) -->
              <q-select
                class="custom-select"
                v-model="form.source_finance_account_id"
                label="Akun Kas Sumber (Asal)"
                :options="sourceAccountOptions"
                map-options
                emit-value
                :errorMessage="form.errors.source_finance_account_id"
                :error="!!form.errors.source_finance_account_id"
                :disable="form.processing"
                hide-bottom-space
                clearable
              >
                <template v-slot:option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.label }}</q-item-label>
                      <q-item-label caption v-if="scope.opt.description">
                        {{ scope.opt.description }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>

              <!-- Akun Tujuan (Ke mana uang disetor) -->
              <q-select
                class="custom-select"
                v-model="form.target_finance_account_id"
                label="Akun Kas Tujuan (Penerima)"
                :options="targetAccountOptions"
                map-options
                emit-value
                :errorMessage="form.errors.target_finance_account_id"
                :error="!!form.errors.target_finance_account_id"
                :disable="form.processing"
                hide-bottom-space
                clearable
              >
                <template v-slot:option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.label }}</q-item-label>
                      <q-item-label caption v-if="scope.opt.description">
                        {{ scope.opt.description }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>

              <!-- Jumlah -->
              <LocaleNumberInput
                v-model:modelValue="form.amount"
                label="Jumlah Fisik Uang (Rp.)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.amount"
                :errorMessage="form.errors.amount"
                :rules="[(val) => val > 0 || 'Jumlah harus lebih dari 0']"
                hide-bottom-space
              />

              <!-- Catatan -->
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Keterangan / Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                hide-bottom-space
              />

              <!-- Bukti Foto -->
              <ImageUpload
                v-model="form.image"
                label="Bukti Foto (Struk/Uang Fisik)"
                :disabled="form.processing"
                :error="!!form.errors.image"
                :error-message="form.errors.image"
              />
            </q-card-section>

            <q-card-section class="q-gutter-sm row">
              <q-btn
                icon="save"
                type="submit"
                label="Buat Pengajuan"
                color="primary"
                :disable="form.processing"
                :loading="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="$inertia.get(route('admin.cashier-cash-drop.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<script setup>
import { computed, ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit, transformPayload } from "@/helpers/client-req-handler";
import { useSupplierFilter } from "@/composables/useSupplierFilter";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import ImageUpload from "@/components/ImageUpload.vue";

const page = usePage();
// Judul dinamis tergantung apakah edit atau buat baru
const title =
  (!!page.props.data.id ? "Edit" : "Catat") +
  " Transaksi Utang / Piutang Manual";

// Composable untuk filter dropdown supplier
const { filteredSuppliers, filterSuppliersFn } = useSupplierFilter(
  page.props.suppliers,
  false
);

// Transformasi props 'types' (Object) menjadi Array untuk QSelect
const typeOptions = computed(() => {
  return Object.entries(page.props.types).map(([value, label]) => ({
    label: label,
    value: value,
  }));
});

// Mapping Akun Keuangan
const finance_accounts = page.props.finance_accounts.map((account) => ({
  label: account.name,
  value: account.id,
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

// Helper untuk label dinamis pada Akun Keuangan
const financeAccountLabel = computed(() => {
  if (form.type === "payment") return "Keluar dari Kas (Opsional)";
  if (form.type === "return") return "Masuk ke Kas (Opsional)";
  return "Akun Keuangan Terkait (Opsional)";
});

const submit = () => {
  transformPayload(form, { datetime: "YYYY-MM-DD HH:mm:ss" });
  handleSubmit({ form, url: route("admin.supplier-ledger.save") });
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
          @click="$inertia.get(route('admin.supplier-ledger.index'))"
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
            <q-card-section class="q-pt-md">
              <input type="hidden" name="id" v-model="form.id" />

              <!-- Tanggal Transaksi -->
              <date-time-picker
                autofocus
                v-model="form.datetime"
                label="Tanggal Transaksi"
                :error="!!form.errors.datetime"
                :disable="form.processing"
                hide-bottom-space
              />

              <!-- Pilih Supplier -->
              <q-select
                class="custom-select"
                v-model="form.supplier_id"
                label="Supplier"
                use-input
                input-debounce="300"
                clearable
                :options="filteredSuppliers"
                map-options
                emit-value
                :error-message="form.errors.supplier_id"
                @filter="filterSuppliersFn"
                :error="!!form.errors.supplier_id"
                :disable="form.processing"
                hide-bottom-space
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey"
                      >Supplier tidak ditemukan</q-item-section
                    >
                  </q-item>
                </template>
              </q-select>

              <!-- Jenis Transaksi (Opening Balance, Payment, dll) -->
              <q-select
                v-model="form.type"
                label="Jenis Transaksi"
                :options="typeOptions"
                map-options
                emit-value
                :error="!!form.errors.type"
                :disable="form.processing"
                :error-message="form.errors.type"
                hide-bottom-space
              />

              <!-- Jumlah (Absolut) -->
              <LocaleNumberInput
                v-model:modelValue="form.amount"
                label="Nominal (Rp)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.amount"
                :error-message="form.errors.amount"
                :allow-negative="true"
                hide-bottom-space
              />

              <!-- Akun Keuangan (Opsional) -->
              <q-select
                v-model="form.finance_account_id"
                :label="financeAccountLabel"
                :options="finance_accounts"
                map-options
                emit-value
                clearable
                :error="!!form.errors.finance_account_id"
                :disable="form.processing"
                :error-message="form.errors.finance_account_id"
                hide-bottom-space
              >
                <template v-slot:hint>
                  Pilih jika transaksi ini melibatkan uang kas secara langsung.
                </template>
              </q-select>

              <!-- Catatan -->
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Catatan / Keterangan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                hide-bottom-space
              />

              <!-- Upload Bukti -->
              <ImageUpload
                v-model="form.image"
                :initial-image-path="form.image_path"
                :disabled="form.processing"
                :error="!!form.errors.image || !!form.errors.image_path"
                :error-message="form.errors.image || form.errors.image_path"
                label="Bukti Transaksi (Struk/Nota)"
              />
            </q-card-section>

            <q-card-section class="q-gutter-sm flex">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan"
                color="primary"
                :loading="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="$inertia.get(route('admin.supplier-ledger.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

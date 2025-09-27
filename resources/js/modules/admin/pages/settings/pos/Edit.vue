<script setup>
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useForm, usePage } from "@inertiajs/vue3";

const page = usePage();
const form = useForm({
  foot_note: page.props.data.foot_note,
  default_payment_mode: page.props.data.default_payment_mode,
  default_print_size: page.props.data.default_print_size,
});

const submit = () =>
  handleSubmit({
    form,
    url: route("admin.pos-settings.edit"),
  });

const paymentModeOptions = [
  {
    value: "cash",
    label: "Tunai",
  },
  {
    value: "wallet",
    label: "Wallet",
  },
];

const printSizeOptions = [
  {
    value: "58mm",
    label: "Struk 58mm",
  },
  {
    value: "a4",
    label: "Nota A4",
  },
];
</script>

<template>
  <i-head title="Pengaturan POS" />
  <authenticated-layout>
    <template #title>Pengaturan POS</template>
    <div class="q-pa-xs">
      <div class="row justify-center">
        <div class="col col-md-6">
          <q-form
            class="row"
            @submit.prevent="submit"
            @validation-error="scrollToFirstErrorField"
          >
            <q-card flat bordered square class="col">
              <q-card-section>
                <div class="text-subtitle1 q-my-xs">
                  Pengaturan POS (Point of Sales)
                </div>
                <p class="text-caption text-grey-9">Perbarui pengaturan POS.</p>
                <q-select
                  v-model="form.default_payment_mode"
                  label="Default Pembayaran"
                  :options="paymentModeOptions"
                  map-options
                  emit-value
                  lazy-rules
                  :disable="form.processing"
                  transition-show="jump-up"
                  transition-hide="jump-up"
                  :error="!!form.errors.default_payment_mode"
                  :error-message="form.errors.default_payment_mode"
                  hide-bottom-space
                />
                <q-select
                  v-model="form.default_print_size"
                  label="Default Ukuran Cetak"
                  :options="printSizeOptions"
                  map-options
                  emit-value
                  lazy-rules
                  :disable="form.processing"
                  transition-show="jump-up"
                  transition-hide="jump-up"
                  :error="!!form.errors.default_print_size"
                  :error-message="form.errors.default_print_size"
                  hide-bottom-space
                />
                <q-input
                  type="textarea"
                  counter
                  autogrow
                  maxlength="500"
                  v-model.trim="form.foot_note"
                  label="Foot Note"
                  :disable="form.processing"
                  lazy-rules
                  :error="!!form.errors.foot_note"
                  :error-message="form.errors.foot_note"
                  hide-bottom-space
                />
              </q-card-section>
              <q-card-section>
                <q-btn
                  icon="save"
                  type="submit"
                  color="primary"
                  label="Simpan"
                  :disable="form.processing"
                  :loading="form.processing"
                />
              </q-card-section>
            </q-card>
          </q-form>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>

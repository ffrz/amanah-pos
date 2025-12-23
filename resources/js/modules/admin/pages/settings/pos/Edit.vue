<script setup>
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const page = usePage();
const currentTab = ref("personal");

const form = useForm({
  // Tab Personal
  default_payment_mode: page.props.data.default_payment_mode,
  default_print_size: page.props.data.default_print_size,
  after_payment_action: page.props.data.after_payment_action,

  // Tab General
  foot_note: page.props.data.foot_note,
  allow_negative_inventory: !!parseInt(
    page.props.data.allow_negative_inventory
  ),
  allow_credit_limit: !!parseInt(page.props.data.allow_credit_limit),
  allow_selling_at_loss: !!parseInt(page.props.data.allow_selling_at_loss),
});

const submit = () =>
  handleSubmit({
    form,
    url: route("admin.pos-settings.edit"),
  });

const paymentModeOptions = [
  { value: "cash", label: "Tunai" },
  { value: "wallet", label: "Wallet" },
];

const printSizeOptions = [
  { value: "58mm", label: "Struk 58mm" },
  { value: "80mm", label: "Struk 80mm" },
  { value: "a4", label: "Nota A4" },
];

const afterPaymentActionOptions = [
  { value: "print", label: "Cetak" },
  { value: "detail", label: "Rincian Order" },
  { value: "new-order", label: "Order Baru" },
];
</script>

<template>
  <i-head title="Pengaturan POS" />
  <authenticated-layout>
    <template #title>Pengaturan POS</template>
    <div class="q-pa-xs">
      <div class="row justify-center">
        <div class="col col-md-7 col-lg-6">
          <q-form
            class="row"
            @submit.prevent="submit"
            @validation-error="scrollToFirstErrorField"
          >
            <q-card flat bordered square class="col column">
              <q-card-section class="bg-grey-1">
                <div class="text-subtitle1 text-bold">
                  Konfigurasi Sistem POS
                </div>
                <div class="text-caption text-grey-7">
                  Sesuaikan pengaturan Point of Sales (POS).
                </div>
              </q-card-section>

              <q-separator />

              <q-tabs
                v-model="currentTab"
                dense
                class="text-grey"
                active-color="primary"
                indicator-color="primary"
                align="justify"
                narrow-indicator
              >
                <q-tab name="personal" icon="person" label="Personal" />
                <q-tab
                  name="general"
                  icon="settings"
                  label="Umum"
                  v-if="page.props.auth.user.type == 'super_user'"
                />
              </q-tabs>

              <q-separator />

              <q-tab-panels v-model="currentTab" animated>
                <q-tab-panel name="personal" class="q-gutter-y-md">
                  <div
                    class="bg-blue-1 q-pa-sm rounded-borders text-blue-9 text-caption q-mb-md"
                  >
                    <q-icon name="info" /> Pengaturan ini berlaku hanya untuk
                    anda.
                  </div>
                  <q-select
                    v-model="form.default_payment_mode"
                    label="Metode Pembayaran Default"
                    :options="paymentModeOptions"
                    map-options
                    emit-value
                    outlined
                    dense
                    hide-bottom-space
                    :disable="form.processing"
                    :error="!!form.errors.default_payment_mode"
                    :error-message="form.errors.default_payment_mode"
                  />
                  <q-select
                    v-model="form.default_print_size"
                    label="Ukuran Cetak Default"
                    :options="printSizeOptions"
                    map-options
                    emit-value
                    outlined
                    dense
                    hide-bottom-space
                    :disable="form.processing"
                    :error="!!form.errors.default_print_size"
                    :error-message="form.errors.default_print_size"
                  />
                  <q-select
                    v-model="form.after_payment_action"
                    label="Aksi Otomatis Setelah Bayar"
                    :options="afterPaymentActionOptions"
                    map-options
                    emit-value
                    outlined
                    dense
                    hide-bottom-space
                    :disable="form.processing"
                    :error="!!form.errors.after_payment_action"
                    :error-message="form.errors.after_payment_action"
                  />
                </q-tab-panel>

                <q-tab-panel name="general" class="q-gutter-y-sm">
                  <div
                    class="bg-blue-1 q-pa-sm rounded-borders text-blue-9 text-caption q-mb-md"
                  >
                    <q-icon name="info" /> Pengaturan ini berlaku secara global
                    untuk semua pengguna.
                  </div>

                  <q-list dense>
                    <q-item tag="label" v-ripple>
                      <q-item-section>
                        <q-item-label>Izinkan Stok Minus</q-item-label>
                        <q-item-label caption
                          >Bisa jualan meskipun stok di sistem 0 atau
                          habis.</q-item-label
                        >
                      </q-item-section>
                      <q-item-section side>
                        <q-toggle
                          v-model="form.allow_negative_inventory"
                          color="primary"
                        />
                      </q-item-section>
                    </q-item>

                    <q-item tag="label" v-ripple>
                      <q-item-section>
                        <q-item-label>Izinkan Lewati Limit Kredit</q-item-label>
                        <q-item-label caption
                          >Izinkan piutang pelanggan melebihi batas
                          limitnya.</q-item-label
                        >
                      </q-item-section>
                      <q-item-section side>
                        <q-toggle
                          v-model="form.allow_credit_limit"
                          color="primary"
                        />
                      </q-item-section>
                    </q-item>

                    <q-item tag="label" v-ripple>
                      <q-item-section>
                        <q-item-label>Izinkan Jual Rugi</q-item-label>
                        <q-item-label caption
                          >Izinkan harga jual lebih rendah dari harga modal
                          (HPP).</q-item-label
                        >
                      </q-item-section>
                      <q-item-section side>
                        <q-toggle
                          v-model="form.allow_selling_at_loss"
                          color="primary"
                        />
                      </q-item-section>
                    </q-item>
                  </q-list>

                  <q-separator class="q-my-md" />

                  <q-input
                    type="textarea"
                    label="Catatan Kaki (Foot Note)"
                    placeholder="Contoh: Barang yang sudah dibeli tidak dapat ditukar..."
                    v-model.trim="form.foot_note"
                    counter
                    autogrow
                    maxlength="500"
                    hint="Akan muncul di bagian bawah struk belanja."
                    :disable="form.processing"
                    :error="!!form.errors.foot_note"
                    :error-message="form.errors.foot_note"
                  />
                </q-tab-panel>
              </q-tab-panels>

              <q-separator />

              <q-card-section class="text-right">
                <q-btn
                  icon="save"
                  type="submit"
                  color="primary"
                  label="Simpan Perubahan"
                  class="full-width"
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

<style scoped>
/* Styling opsional untuk merapikan tampilan q-item-label */
.q-item__section--side > .q-toggle {
  margin-right: -12px;
}
</style>

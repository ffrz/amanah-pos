<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import InvoiceTab from "./detail/InvoiceTab.vue";
import PaymentTab from "./detail/PaymentTab.vue";

const urlParams = new URLSearchParams(window.location.search);
const page = usePage();
const data = page.props.data;
const title = `Rincian Penjualan`;
const isPreview = ref(urlParams.get("preview") || false);

const getInitialTab = () => {
  return urlParams.get("tab") || "invoice";
};
const currentTab = ref(getInitialTab());

const print = () => {
  window.print();
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>
      <div class="row items-center q-gutter-x-sm">
        <span>{{ title }}</span>
      </div>
    </template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$goBack()"
        />
      </div>
    </template>
    <template #right-button>
      <q-btn icon="print" dense color="primary" flat rounded @click="print()" />
      <q-btn
        v-if="!isPreview"
        class="q-ml-sm"
        icon="add"
        dense
        color="primary"
        rounded
        @click="router.get(route('admin.sales-order.add'))"
      />
    </template>

    <q-page class="row justify-center print-visible">
      <div class="col col-lg-6 q-pa-xs" align="center">
        <q-card
          square
          flat
          bordered
          class="full-width"
          style="max-width: 1024px"
        >
          <q-tabs
            v-model="currentTab"
            dense
            class="text-grey-7"
            active-color="primary"
            indicator-color="primary"
            align="justify"
            narrow-indicator
          >
            <q-tab name="invoice" label="Invoice" />
            <q-tab name="payment" label="Pembayaran" v-if="!isPreview" />
          </q-tabs>

          <q-separator />

          <q-tab-panels v-model="currentTab" animated>
            <q-tab-panel name="invoice" class="q-pa-none">
              <invoice-tab :data="data" />
            </q-tab-panel>

            <q-tab-panel name="payment" class="q-pa-none">
              <payment-tab :data="data" />
            </q-tab-panel>
          </q-tab-panels>
        </q-card>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<style scoped>
@media print {
  .q-btn,
  .q-header,
  .q-footer,
  .q-drawer-container,
  .no-print {
    display: none !important;
  }
}
</style>

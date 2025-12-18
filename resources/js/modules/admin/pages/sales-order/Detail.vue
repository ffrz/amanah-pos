<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { nextTick, onMounted, onUnmounted, ref } from "vue";
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

const handleKeyDown = (e) => {
  if (e.ctrlKey && (e.key === "'" || e.key === '"')) {
    e.stopPropagation();
    e.preventDefault();
    print("default");
    return;
  }

  if (isPreview.value) return;

  if (e.ctrlKey && e.key === "Enter") {
    newOrder();
    e.stopPropagation();
    e.preventDefault();
    return;
  }
};

onMounted(() => {
  nextTick(() => {
    print();
  });

  document.addEventListener("keydown", handleKeyDown, true);
});

onUnmounted(() => {
  document.removeEventListener("keydown", handleKeyDown, true);
});

const newOrder = () => {
  router.get(route("admin.sales-order.add"));
};

const print = (size) => {
  if (!size) {
    const printSize = urlParams.get("print_size");
    if (!printSize) return;
    size = printSize;
  }

  window.open(
    route("admin.sales-order.print", { id: page.props.data.id, size: size }),
    "_self"
  );
};

const getWatermarkClass = () => {
  if (currentTab.value != "invoice") {
    return "";
  }
  if (page.props.data.status == "canceled") {
    return "canceled-label";
  }
  if (isPreview.value) {
    return "draft-label";
  }
  if (page.props.data.payment_status == "fully_paid") {
    return "paid-label";
  }
  return "unpaid-label";
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
          @click="
            isPreview
              ? router.get(route('admin.sales-order.edit', page.props.data.id))
              : router.get(route('admin.sales-order.index'))
          "
        />
      </div>
    </template>
    <template #right-button>
      <q-btn
        icon="receipt"
        dense
        color="primary"
        flat
        rounded
        @click="print('58mm')"
      />
      <q-btn
        icon="print"
        dense
        color="primary"
        flat
        rounded
        @click="print('a4')"
      />
      <q-btn
        v-if="!isPreview"
        class="q-ml-sm"
        icon="add"
        dense
        color="primary"
        rounded
        @click="newOrder()"
      />
    </template>

    <q-page class="row justify-center print-visible">
      <div class="col col-lg-6 q-pa-xs" align="center">
        <q-card
          square
          flat
          bordered
          class="full-width"
          :class="getWatermarkClass()"
          style="max-width: 1024px"
        >
          <q-tabs
            v-model="currentTab"
            dense
            class="text-grey-7"
            active-color="primary"
            indicator-color="primary"
            align="justify"
            switch-indicator
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

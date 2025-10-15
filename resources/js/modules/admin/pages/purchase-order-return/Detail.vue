<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { nextTick, onMounted, ref } from "vue";
import MainInfoTab from "./detail/MainInfoTab.vue";
import RefundTab from "./detail/RefundTab.vue";

const urlParams = new URLSearchParams(window.location.search);
const page = usePage();
const data = page.props.data;
const title = `Retur Pembelian`;
const isPreview = ref(urlParams.get("preview") || false);

const getInitialTab = () => {
  return urlParams.get("tab") || "main-info";
};

const currentTab = ref(getInitialTab());

onMounted(() => {
  nextTick(() => {
    const printSize = urlParams.get("print_size");
    if (printSize) {
      print(printSize);
    }
  });
});

const print = (size) => {
  window.open(
    route("admin.purchase-order-return.print", {
      id: page.props.data.id,
      size: size,
    }),
    "_self"
  );
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
        @click="router.get(route('admin.purchase-order.add'))"
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
            <q-tab name="main-info" label="Info Retur" />
            <q-tab name="refund" label="Refund Pembayaran" v-if="!isPreview" />
          </q-tabs>

          <q-separator />

          <q-tab-panels v-model="currentTab" animated>
            <q-tab-panel name="main-info" class="q-pa-none">
              <main-info-tab :data="data" />
            </q-tab-panel>

            <q-tab-panel name="refund" class="q-pa-none">
              <refund-tab :data="data" />
            </q-tab-panel>
          </q-tab-panels>
        </q-card>
      </div>
    </q-page>
  </authenticated-layout>
</template>

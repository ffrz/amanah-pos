<script setup>
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import MainInfoTab from "./detail/MainInfoTab.vue";
import SalesOrderTab from "./detail/SalesOrderTab.vue";
import FinanceTransactionTab from "./detail/FinanceTransactionTab.vue";

const page = usePage();
const title = "Rincian Sesi Kasir";
const data = computed(() => page.props.data);
const tab = ref("main");

watch(
  () => page.props.tab,
  (newTab) => {
    if (newTab) {
      tab.value = newTab;
    }
  },
  { immediate: true }
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
      <div class="col col-lg-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col q-pa-none q-pt-md">
            <q-tabs
              v-model="tab"
              dense
              class="text-grey-7"
              active-color="primary"
              indicator-color="primary"
              align="justify"
              narrow-indicator
            >
              <q-tab name="main" label="Ringkasan" />
              <q-tab name="sales" label="Transaksi Penjualan" />
              <q-tab name="finance" label="Transaksi Keuangan" />
            </q-tabs>
            <q-separator />
            <q-tab-panels v-model="tab" animated class="q-pa-none">
              <q-tab-panel name="main">
                <MainInfoTab :data="data" />
              </q-tab-panel>
              <q-tab-panel name="sales" class="q-pa-xs">
                <SalesOrderTab :data="data" />
              </q-tab-panel>
              <q-tab-panel name="finance" class="q-pa-xs">
                <FinanceTransactionTab :data="data" />
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

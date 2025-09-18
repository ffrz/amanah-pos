<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { ref } from "vue";
import MainInfoTab from "./detail/MainInfoTab.vue";
import WalletHistoryTab from "./detail/WalletHistoryTab.vue";
import OrderHistoryTab from "./detail/OrderHistoryTab.vue";

const page = usePage();
const title = "Rincian Pelanggan";
const tab = ref("main");

const $q = useQuasar();
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
          @click="$inertia.get(route('admin.customer.index'))"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-tabs v-model="tab" align="left">
              <q-tab name="main" label="Info Utama" />
              <q-tab name="history" label="Riwayat Wallet" />
              <q-tab name="order-history" label="Riwayat Order" />
            </q-tabs>
            <q-tab-panels v-model="tab">
              <q-tab-panel name="main">
                <MainInfoTab />
              </q-tab-panel>
              <q-tab-panel name="history" class="q-pa-xs">
                <WalletHistoryTab />
              </q-tab-panel>
              <q-tab-panel name="order-history" class="q-pa-xs">
                <OrderHistoryTab />
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

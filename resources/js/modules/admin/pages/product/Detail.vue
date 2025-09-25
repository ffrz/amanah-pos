<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import MainInfoTab from "./detail/MainInfoTab.vue";
import StockHistoryTab from "./detail/StockHistoryTab.vue";

const page = usePage();
const title = `Rincian Produk #${page.props.data.id}`;
const tab = ref("main");
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="false">
    <template #title>{{ title }}</template>
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
      <div class="q-gutter-sm">
        <q-btn
          v-if="$can('admin.product.edit')"
          icon="edit"
          size="sm"
          dense
          flat
          rounded
          color="grey"
          @click="
            router.get(route('admin.product.edit', { id: page.props.data.id }))
          "
        />
      </div>
    </template>

    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="q-card col">
            <q-tabs v-model="tab" align="left">
              <q-tab name="main" label="Info Utama" />
              <q-tab name="history" label="Riwayat Stok" />
            </q-tabs>
            <q-tab-panels v-model="tab">
              <q-tab-panel name="main">
                <MainInfoTab :product="page.props.data" />
              </q-tab-panel>

              <q-tab-panel name="history" class="q-pa-xs">
                <StockHistoryTab :product-id="page.props.data.id" />
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>

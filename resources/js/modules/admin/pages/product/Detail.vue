<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import MainInfoTab from "./detail/MainInfoTab.vue";
import StockHistoryTab from "./detail/StockHistoryTab.vue";
// import DocumentVersionHistoryList from "../../../../components/DocumentVersionHistoryList.vue";

const page = usePage();
const title = `Rincian Produk #${page.props.data.id}`;
const tab = ref("main");
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.product.index'))"
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
          title="Edit produk"
        />
        <q-btn
          v-if="$can('admin.product.add')"
          icon="content_copy"
          size="sm"
          dense
          flat
          rounded
          color="grey"
          @click="
            router.get(
              route('admin.product.duplicate', { id: page.props.data.id })
            )
          "
          title="Duplikat produk"
        />
        <q-btn
          v-if="$can('admin.product.add')"
          icon="add"
          size="sm"
          dense
          rounded
          color="primary"
          @click="router.get(route('admin.product.add'))"
          title="Tambah produk baru"
        />
      </div>
    </template>

    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-tabs
              v-model="tab"
              dense
              class="text-grey-7"
              active-color="primary"
              indicator-color="primary"
              align="justify"
              switch-indicator
            >
              <q-tab name="main" label="Info Utama" />
              <q-tab name="history" label="Riwayat Stok" />
              <!-- <q-tab name="version-history" label="Riwayat Dokumen" /> -->
            </q-tabs>
            <q-tab-panels v-model="tab" animated>
              <q-tab-panel name="main">
                <MainInfoTab :product="page.props.data" />
              </q-tab-panel>

              <q-tab-panel name="history" class="q-pa-xs">
                <StockHistoryTab :product-id="page.props.data.id" />
              </q-tab-panel>
              <!-- <q-tab-panel name="version-history" class="q-pa-xs">
                <DocumentVersionHistoryList
                  :document-id="page.props.data.id"
                  document-type="product"
                />
              </q-tab-panel> -->
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>

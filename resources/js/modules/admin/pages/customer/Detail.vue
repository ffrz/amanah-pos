<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { ref } from "vue";
import MainInfoTab from "./detail/MainInfoTab.vue";
import WalletHistoryTab from "./detail/WalletHistoryTab.vue";
import OrderHistoryTab from "./detail/OrderHistoryTab.vue";
import LedgerHistoryTab from "./detail/LedgerHistoryTab.vue";
// import DocumentVersionHistoryList from "@/components/DocumentVersionHistoryList.vue";

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
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          v-if="$can('admin.customer.edit')"
          icon="edit"
          size="sm"
          dense
          flat
          rounded
          color="grey"
          @click="
            router.get(route('admin.customer.edit', { id: page.props.data.id }))
          "
          title="Edit Pelanggan"
        />
        <q-btn
          v-if="$can('admin.customer.add')"
          icon="content_copy"
          size="sm"
          dense
          flat
          rounded
          color="grey"
          @click="
            router.get(route('admin.customer.edit', { id: page.props.data.id }))
          "
          title="Duplikat pelanggan"
        />
        <q-btn
          v-if="$can('admin.customer.add')"
          icon="add"
          size="sm"
          dense
          rounded
          color="primary"
          @click="router.get(route('admin.customer.add'))"
          title="Tambah pelanggan baru"
        />
      </div>
    </template>
    <q-page class="row justify-center">
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
              <q-tab name="wallet-history" label="Riwayat Deposit" />
              <q-tab name="ledger-history" label="Riwayat Piutang" />
              <q-tab name="order-history" label="Riwayat Order" />
              <!-- <q-tab name="version-history" label="Riwayat Dokumen" /> -->
            </q-tabs>
            <q-tab-panels v-model="tab" animated>
              <q-tab-panel name="main">
                <MainInfoTab />
              </q-tab-panel>
              <q-tab-panel name="wallet-history" class="q-pa-xs">
                <WalletHistoryTab />
              </q-tab-panel>
              <q-tab-panel name="ledger-history" class="q-pa-xs">
                <LedgerHistoryTab />
              </q-tab-panel>
              <q-tab-panel name="order-history" class="q-pa-xs">
                <OrderHistoryTab />
              </q-tab-panel>

              <!-- <q-tab-panel name="version-history" class="q-pa-xs">
                <DocumentVersionHistoryList
                  :document-id="page.props.data.id"
                  document-type="customer"
                />
              </q-tab-panel> -->
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

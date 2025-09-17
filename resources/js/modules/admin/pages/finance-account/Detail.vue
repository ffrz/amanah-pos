<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import MainInfoTab from "./detail/MainInfoTab.vue";
import HistoryTab from "./detail/HistoryTab.vue";

const page = usePage();
const title = "Rincian Akun";
const tab = ref("main");
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
          @click="$inertia.get(route('admin.finance-account.index'))"
        />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="edit"
          size="sm"
          dense
          flat
          rounded
          color="grey"
          @click="
            router.get(
              route('admin.finance-account.edit', { id: page.props.data.id })
            )
          "
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-tabs v-model="tab" align="left">
              <q-tab name="main" label="Info Akun" />
              <q-tab name="history" label="Riwayat Transaksi" />
            </q-tabs>
            <q-tab-panels v-model="tab">
              <q-tab-panel name="main">
                <MainInfoTab />
              </q-tab-panel>
              <q-tab-panel name="history" class="q-pa-xs">
                <HistoryTab />
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<script setup>
import { ref } from "vue";
import MainInfoTab from "./detail/MainInfoTab.vue";
import SessionHistoryTab from "./detail/SessionHistoryTab.vue";

const title = "Rincian Terminal Kasir";
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
          @click="$inertia.get(route('admin.cashier-terminal.index'))"
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
              narrow-indicator
            >
              <q-tab name="main" label="Info Utama" />
              <q-tab name="session-history" label="Riwayat Sesi Kasir" />
            </q-tabs>
            <q-tab-panels v-model="tab" animated>
              <q-tab-panel name="main">
                <MainInfoTab />
              </q-tab-panel>
              <q-tab-panel name="session-history" class="q-pa-xs">
                <SessionHistoryTab />
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

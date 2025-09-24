<script setup>
import { ref } from "vue";
import MainInfoTab from "./detail/MainInfoTab.vue";
// import SessionHistoryTab from "./detail/SessionHistoryTab.vue";

const title = "Rincian Sesi Kasir";
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
          @click="$inertia.get(route('admin.cashier-session.index'))"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col q-pa-sm q-pt-md">
            <MainInfoTab />
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { useQuasar } from "quasar"; // Import useQuasar untuk deteksi breakpoint

const $q = useQuasar();
const title = "Laporan";
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button> </template>

    <div class="q-pa-xs">
      <q-card flat bordered class="full-width">
        <div>Laporan Pelanggan</div>
        <div></div>
      </q-card>
    </div>
  </authenticated-layout>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";

const page = usePage();
const title = "Laporan Supplier";
const options = page.props.options;

const statusOptions = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <BackButton :route="route('admin.report.index')" />
      </div>
    </template>
    <template #right-button></template>

    <ReportGeneratorLayout
      :options="options"
      routeName="admin.report.supplier.generate"
    >
      <template #filter="{ form }">
        <q-select
          label="Status"
          v-model="form.filter.status"
          :options="statusOptions"
          map-options
        />
      </template>
    </ReportGeneratorLayout>
  </authenticated-layout>
</template>

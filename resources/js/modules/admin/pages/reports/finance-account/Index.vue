<script setup>
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Laporan Akun Keuangan";
const options = page.props.options;

const statusOptions = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const typeOptions = [
  { value: "all", label: "Semua" },
  { value: "cash", label: "Kas Besar" },
  { value: "bank", label: "Bank" },
  { value: "petty_cash", label: "Kas Kecil" },
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
      routeName="admin.report.finance-account.generate"
    >
      <template #filter="{ form }">
        <q-select
          label="Status"
          v-model="form.filter.status"
          :options="statusOptions"
          map-options
          emit-value
        />
        <q-select
          label="Jenis Akun"
          v-model="form.filter.type"
          :options="typeOptions"
          map-options
          emit-value
        />
      </template>
    </ReportGeneratorLayout>
  </authenticated-layout>
</template>

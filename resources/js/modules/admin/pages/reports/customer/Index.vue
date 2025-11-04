<script setup>
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Laporan Pelanggan";
const options = page.props.options;

const statusOptions = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const typeOptions = [
  { value: "all", label: "Semua" },
  { value: "general", label: "Umum" },
  { value: "staff", label: "Staff" },
  { value: "category1", label: "Kategori 1" },
  { value: "category2", label: "Kategori 2" },
  { value: "category3", label: "Kategori 3" },
];

const priceOptions = [
  { value: "all", label: "Semua" },
  { value: "price_1", label: "Harga Eceran" },
  { value: "price_2", label: "Harga Partai" },
  { value: "price_3", label: "Harga Grosir" },
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
      routeName="admin.report.customer.generate"
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
          label="Level Harga"
          v-model="form.filter.default_price_type"
          :options="priceOptions"
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

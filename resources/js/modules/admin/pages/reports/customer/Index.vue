<script setup>
import { ref } from "vue";
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";
import { useQuasar } from "quasar";
import { createOptions } from "@/helpers/options";
import { usePage } from "@inertiajs/vue3";

const $q = useQuasar();
const page = usePage();
const title = "Laporan Pelanggan";

const primaryColumns = createOptions(page.props.primary_columns);
const optionalColumns = createOptions(page.props.optional_columns);
const initialColumns = page.props.initial_columns;

console.log(page.props);

const statusOptions = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const typeOptions = [
  { value: "all", label: "Semua" },
  { value: "general", label: "Umum" },
  { value: "staff", label: "Staff" },
  { value: "category_1", label: "Kategori 1" },
  { value: "category_2", label: "Kategori 2" },
  { value: "category_3", label: "Kategori 3" },
];

const priceOptions = [
  { value: "all", label: "Semua" },
  { value: "price_1", label: "Harga Eceran" },
  { value: "price_2", label: "Harga Partai" },
  { value: "price_3", label: "Harga Grosir" },
];

const initialFilter = {
  status: "all",
  type: "all",
  default_price_type: "all",
};

const initialSortOptions = [
  {
    column: "code",
    order: "asc",
  },
];

const reportGeneratorRef = ref(null);

const handleReportSubmit = ({ format, form }) => {
  const params = {
    ...form,
    format: format,
  };

  try {
    const url = route("admin.report.customer.list", params);

    window.open(url, "_blank");

    if (format !== "html") {
      $q.notify({
        type: "positive",
        message: `Laporan ${format.toUpperCase()} sedang diunduh.`,
        timeout: 2000,
      });
    }
  } catch (error) {
    $q.notify({
      type: "negative",
      message: "Gagal menghasilkan URL laporan. [ROUTE ERROR]",
      timeout: 5000,
    });
    console.error("Route error:", error);
  }
};
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
      ref="reportGeneratorRef"
      :primaryColumns="primaryColumns"
      :optionalColumns="optionalColumns"
      :initialColumns="initialColumns"
      :initialFilter="initialFilter"
      :initialSortOptions="initialSortOptions"
      @submit="handleReportSubmit"
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

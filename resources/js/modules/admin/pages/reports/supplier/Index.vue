<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";
import { useQuasar } from "quasar";
import { createOptions } from "@/helpers/options";

const $q = useQuasar();
const page = usePage();
const title = "Laporan Supplier";

const primaryColumns = createOptions(page.props.primary_columns);
const optionalColumns = createOptions(page.props.optional_columns);
const initialColumns = page.props.initial_columns;

const statusOptions = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const initialFilter = {
  status: "all",
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
    const url = route("admin.report.supplier.list", params);

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
        />
      </template>
    </ReportGeneratorLayout>
  </authenticated-layout>
</template>

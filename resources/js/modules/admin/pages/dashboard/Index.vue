<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { getQueryParams } from "@/helpers/utils";
import ActualSummaryCard from "./cards/ActualSummaryCard.vue";
import ChartCard from "./cards/ChartCard.vue";
import TopCard from "./cards/TopCard.vue";

const page = usePage();

const dashboardData = ref({
  top_customers_sales: [
    { name: "Wildan Medina", value: 1822000 },
    { name: "Syafiq", value: 1754500 },
    { name: "Umar", value: 1534000 },
    { name: "Fauzan", value: 1212000 },
    { name: "Rizky", value: 981500 },
  ],
  top_customers_topup: [
    { name: "Abdullah", value: 1750000 },
    { name: "Abdurrahman", value: 1500000 },
    { name: "Zayd", value: 1250000 },
    { name: "Syafiq", value: 800000 },
    { name: "Umar", value: 650000 },
  ],
  monthly_sales: {
    labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun"],
    data: [14123000, 21344000, 15155000, 17128000, 12557000, 12723000],
  },
  transaction_type_distribution: [
    { value: 500, name: "Deposit" },
    { value: 350, name: "Purchase" },
    { value: 200, name: "Withdrawal" },
    { value: 200, name: "Refund" },
  ],
});

const title = "Dashboard";
const showFilter = ref(true);
const selected_period = ref(getQueryParams()["period"] ?? "this_month");
const period_options = ref([
  { value: "today", label: "Hari Ini" },
  { value: "yesterday", label: "Kemarin" },
  { value: "this_week", label: "Minggu Ini" },
  { value: "last_week", label: "Minggu Lalu" },
  { value: "this_month", label: "Bulan Ini" },
  { value: "prev_month", label: "Bulan Lalu" },
  { value: "prev_2month", label: "2 Bulan Sebelumnya" },
  { value: "prev_3month", label: "3 Bulan Sebelumnya" },
  { value: "this_year", label: "Tahun Ini" },
  { value: "prev_year", label: "Tahun Lalu" },
]);

const onFilterChange = () => {
  router.visit(route("admin.dashboard", { period: selected_period.value }));
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        rounded
        flat
        @click="showFilter = !showFilter"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="custom-select col-12"
            style="min-width: 150px"
            v-model="selected_period"
            :options="period_options"
            label="Periode"
            dense
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
        </div>
      </q-toolbar>
    </template>

    <div class="q-pa-xs">
      <div class="q-pa-none">
        <ActualSummaryCard :dashboardData="dashboardData" />
      </div>
      <ChartCard :dashboardData="dashboardData" />
      <TopCard :dashboardData="dashboardData" />
    </div>
  </authenticated-layout>
</template>

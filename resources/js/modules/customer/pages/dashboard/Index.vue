<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import { getQueryParams } from "@/helpers/utils";
import SummaryCard from "./cards/SummaryCard.vue";
import RecentTopup from "./cards/RecentTopup.vue";
import RecentTransaction from "./cards/RecentTransaction.vue";
import ExpenseSummaryChartCard from "./cards/ExpenseSummaryChartCard.vue";
import DailyExpenseChartCard from "./cards/DailyExpenseChartCard.vue";

const title = "Dashboard";
const showFilter = ref(true);
const selected_month = ref(getQueryParams()["month"] ?? "this_month");

const month_options = ref([
  { value: "this_month", label: "Bulan Ini" },
  { value: "prev_month", label: "1 Bulan Sebelumnya" },
  { value: "prev_2month", label: "2 Bulan Sebelumnya" },
  { value: "prev_3month", label: "3 Bulan Sebelumnya" },
]);

const onFilterChange = () => {
  router.visit(route("customer.dashboard", { month: selected_month.value }));
};

const summaryData = ref({
  balance: 55000,
  total_in: 200000,
  total_out: 145000,
});
</script>

<template>
  <i-head :title="title" />
  <customer-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="custom-select col-12"
            style="min-width: 150px"
            v-model="selected_month"
            :options="month_options"
            label="Bulan"
            dense
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
        </div>
      </q-toolbar>
    </template>

    <div class="q-pa-sm">
      <SummaryCard :summaryData="summaryData" />
    </div>

    <div class="q-pa-sm q-pt-none">
      <div class="row q-col-gutter-sm">
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <ExpenseSummaryChartCard />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <DailyExpenseChartCard />
        </div>
      </div>
    </div>

    <div class="q-pa-sm">
      <div class="row q-col-gutter-sm">
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <RecentTopup />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <RecentTransaction />
        </div>
      </div>
    </div>
  </customer-layout>
</template>

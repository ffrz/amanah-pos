<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { getQueryParams } from "@/helpers/utils";
import SummaryCard from "./cards/SummaryCard.vue";
import WalletTransactionSummaryChartCard from "./cards/WalletTransactionSummaryChartCard.vue";
import MonthlyWalleltTransactionChartCard from "./cards/MonthlyWalleltTransactionChartCard.vue";
import RecentWalletTransactions from "./cards/RecentWalletTransactions.vue";
import RecentPurchaseOrders from "./cards/RecentPurchaseOrders.vue";

const title = "Dashboard";
const showFilter = ref(true);
const selected_month = ref(getQueryParams()["month"] ?? "this_month");
const page = usePage();

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
  balance: page.props.data.actual_balance,
  total_in: page.props.data.total_income,
  total_out: page.props.data.total_expense,
});
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
        flat
        rounded
        size="sm"
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

    <div class="q-pa-xs">
      <SummaryCard :summaryData="summaryData" />
    </div>

    <div class="q-pa-xs q-pt-none">
      <div class="row q-col-gutter-xs">
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <WalletTransactionSummaryChartCard />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <MonthlyWalleltTransactionChartCard />
        </div>
      </div>
    </div>

    <div class="q-pa-xs">
      <div class="row q-col-gutter-xs">
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <RecentWalletTransactions />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <RecentPurchaseOrders />
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>

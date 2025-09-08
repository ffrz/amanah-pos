<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { getQueryParams } from "@/helpers/utils";
import { formatNumber } from "@/helpers/formatter";

import VChart from "vue-echarts";
import { use } from "echarts/core";
import { CanvasRenderer } from "echarts/renderers";
import { BarChart, PieChart } from "echarts/charts";
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
} from "echarts/components";

use([
  CanvasRenderer,
  BarChart,
  PieChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
]);

// Data Dummy
const dashboardData = ref({
  summary: {
    total_santri: 1500,
    active_santri: 1450,
    total_sales: 15500000,
    total_transactions: 1250,
    total_wallet_balance: 55000000,
    total_topup: 75000000,
    total_purchase: 85000000,
    total_withdrawal: 65000000,
  },
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
const showFilter = ref(false);
const selected_month = ref(getQueryParams()["month"] ?? "this_month");
const month_options = ref([
  { value: "this_month", label: "Bulan Ini" },
  { value: "prev_month", label: "1 Bulan Sebelumnya" },
  { value: "prev_2month", label: "2 Bulan Sebelumnya" },
  { value: "prev_3month", label: "3 Bulan Sebelumnya" },
]);

const onFilterChange = () => {
  router.visit(route("admin.dashboard", { month: selected_month.value }));
};

const colors = ["#82B1FF", "#4DB6AC", "#FFB74D", "#9575CD"];

const barChartOptions = computed(() => ({
  title: {
    text: "Penjualan Bulanan",
    left: "center",
  },
  tooltip: {
    trigger: "axis",
    formatter: function (params) {
      let result = params[0].name + "<br/>";
      params.forEach(function (item) {
        result +=
          item.marker +
          " " +
          item.seriesName +
          ": " +
          formatNumber(item.value) +
          "<br/>";
      });
      return result;
    },
  },
  xAxis: {
    type: "category",
    data: dashboardData.value.monthly_sales.labels,
    axisLabel: {
      color: "#616161",
    },
  },
  yAxis: {
    type: "value",
    axisLabel: {
      formatter: (value) => formatNumber(value),
      color: "#616161",
    },
  },
  series: [
    {
      name: "Penjualan",
      type: "bar",
      data: dashboardData.value.monthly_sales.data,
      itemStyle: {
        borderRadius: [5, 5, 0, 0],
        color: colors[0],
      },
    },
  ],
}));

const pieChartOptions = computed(() => ({
  title: {
    text: "Distribusi Jenis Transaksi",
    left: "center",
  },
  tooltip: {
    trigger: "item",
    formatter: function (params) {
      return (
        params.name +
        "<br/>" +
        params.seriesName +
        ": " +
        formatNumber(params.value) +
        " (" +
        params.percent +
        "%)"
      );
    },
  },
  legend: {
    orient: "vertical",
    left: "left",
    textStyle: {
      color: "#616161",
    },
  },
  series: [
    {
      name: "Jumlah Transaksi",
      type: "pie",
      radius: ["40%", "70%"],
      data: dashboardData.value.transaction_type_distribution,
      itemStyle: {
        borderRadius: 5,
        borderColor: "#fff",
        borderWidth: 2,
        color: (params) => {
          return colors[params.dataIndex % colors.length];
        },
      },
      emphasis: {
        itemStyle: {
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowColor: "rgba(0, 0, 0, 0.5)",
        },
      },
    },
  ],
}));

const summaryItems = ref([
  {
    label: "Total Pelanggan",
    value: computed(() => dashboardData.value.summary.total_santri),
    icon: "groups",
    color: "primary",
    bgColor: "#e3f2fd",
  },
  {
    label: "Total Penjualan",
    value: computed(() =>
      formatNumber(dashboardData.value.summary.total_sales)
    ),
    icon: "shopping_cart",
    color: "green-7",
    bgColor: "#e8f5e9",
  },
  {
    label: "Saldo Dompet Pelanggan",
    value: computed(() =>
      formatNumber(dashboardData.value.summary.total_wallet_balance)
    ),
    icon: "account_balance_wallet",
    color: "orange-9",
    bgColor: "#fff3e0",
  },
  {
    label: "Total Transaksi",
    value: computed(() =>
      formatNumber(dashboardData.value.summary.total_transactions)
    ),
    icon: "swap_horiz",
    color: "purple-8",
    bgColor: "#f3e5f5",
  },
]);
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
      <div class="q-pa-none">
        <div class="q-pb-sm">
          <div class="text-h6 text-bold text-grey-8 q-mb-sm">
            Statistik Ringkasan
          </div>
          <div class="row q-col-gutter-sm">
            <div
              v-for="(item, index) in summaryItems"
              :key="index"
              class="col-12 col-md-3"
            >
              <q-card square bordered flat class="q-pa-md" style="width: 100%">
                <div class="row items-center no-wrap">
                  <q-avatar
                    :icon="item.icon"
                    :color="item.color"
                    :text-color="item.bgColor"
                  />
                  <div class="q-ml-md">
                    <div class="text-subtitle2 text-grey-8">
                      {{ item.label }}
                    </div>
                    <div class="text-h6 text-weight-bold">{{ item.value }}</div>
                  </div>
                </div>
              </q-card>
            </div>
          </div>
        </div>
      </div>

      <div class="row q-col-gutter-sm q-pb-sm">
        <div class="col-md-6 col-12">
          <q-card square bordered flat class="full-width q-pa-md">
            <div class="text-h6 q-pb-sm">Penjualan Bulanan</div>
            <VChart class="chart" :option="barChartOptions" autoresize />
          </q-card>
        </div>
        <div class="col-md-6 col-12">
          <q-card square bordered flat class="full-width q-pa-md">
            <div class="text-h6 q-pb-sm">Distribusi Transaksi</div>
            <VChart class="chart" :option="pieChartOptions" autoresize />
          </q-card>
        </div>
      </div>

      <div class="row q-col-gutter-sm q-pb-sm">
        <div class="col-md-6 col-12">
          <q-card square bordered flat class="full-width full-height q-pa-md">
            <div class="text-h6 q-mb-sm">Top 5 Pembelian Pelanggan</div>
            <q-list separator>
              <q-item
                v-for="(item, index) in dashboardData.top_customers_sales"
                :key="index"
              >
                <q-item-section avatar>
                  <q-avatar color="primary" text-color="white" size="sm">{{
                    index + 1
                  }}</q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ item.name }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-item-label class="text-primary">{{
                    formatNumber(item.value)
                  }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>
        <div class="col-md-6 col-12">
          <q-card square bordered flat class="full-width full-height q-pa-md">
            <div class="text-h6 q-mb-sm">Top 5 Top Up Pelanggan</div>
            <q-list separator>
              <q-item
                v-for="(item, index) in dashboardData.top_customers_topup"
                :key="index"
              >
                <q-item-section avatar>
                  <q-avatar color="green-7" text-color="white" size="sm">{{
                    index + 1
                  }}</q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ item.name }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-item-label class="text-green-7">{{
                    formatNumber(item.value)
                  }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>

<style scoped>
.chart {
  height: 350px;
}
</style>

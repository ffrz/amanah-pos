<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import { getQueryParams } from "@/helpers/utils";
import SummaryCard from "./cards/SummaryCard.vue";

// Import ECharts
import VChart from "vue-echarts";
import { use } from "echarts/core";
import { PieChart, BarChart } from "echarts/charts";
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
} from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";

// Hapus LegendComponent karena sudah tidak dipakai
use([
  CanvasRenderer,
  PieChart,
  BarChart,
  TitleComponent,
  TooltipComponent,
  GridComponent,
]);

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

const latestTopups = ref([
  {
    id: 1,
    date: "12-08-2025",
    amount: 100000,
    description: "Top Up Manual",
    status: "Berhasil",
  },
  {
    id: 2,
    date: "01-08-2025",
    amount: 50000,
    description: "Top Up Transfer Bank",
    status: "Berhasil",
  },
  {
    id: 3,
    date: "15-07-2025",
    amount: 50000,
    description: "Top Up Transfer Bank",
    status: "Berhasil",
  },
]);

const latestTransactions = ref([
  {
    id: 1,
    date: "14-08-2025",
    amount: 15000,
    description: "Jajan Kantin",
    type: "keluar",
  },
  {
    id: 2,
    date: "13-08-2025",
    amount: 5000,
    description: "Beli Buku Tulis",
    type: "keluar",
  },
  {
    id: 3,
    date: "12-08-2025",
    amount: 10000,
    description: "Fotokopi Materi",
    type: "keluar",
  },
  {
    id: 4,
    date: "11-08-2025",
    amount: 20000,
    description: "Makan di Koperasi",
    type: "keluar",
  },
]);

const pieChartOption = ref({
  title: {
    text: "Penggunaan Dana Bulan Ini",
    left: "center",
    textStyle: { color: "#444", fontSize: 14 },
  },
  tooltip: {
    trigger: "item",
    formatter: "{a} <br/>{b}: Rp {c} ({d}%)",
  },
  // Hapus bagian 'legend' karena tidak lagi digunakan
  series: [
    {
      name: "Penggunaan Dana",
      type: "pie",
      radius: ["40%", "70%"],
      center: ["50%", "50%"], // Posisikan kembali chart ke tengah
      avoidLabelOverlap: false,
      label: {
        show: true,
        formatter: "{b}: {d}%",
        fontSize: "12",
        fontWeight: "bold",
        color: "#333",
      },
      emphasis: {
        label: {
          show: true,
          fontSize: "15",
          fontWeight: "bold",
        },
      },
      labelLine: {
        show: true,
        length: 10,
        length2: 10,
        lineStyle: {
          color: "#aaa",
        },
      },
      data: [
        { value: 75000, name: "Jajan" },
        { value: 30000, name: "Kebutuhan Harian" },
        { value: 20000, name: "Fotokopi" },
        { value: 20000, name: "Lainnya" },
      ],
    },
  ],
});

const barChartOption = ref({
  title: {
    text: "Transaksi Mingguan",
    left: "center",
    textStyle: { color: "#444", fontSize: 14 },
  },
  tooltip: {
    trigger: "axis",
    axisPointer: { type: "shadow" },
    formatter: (params) => {
      let value = params[0].value;
      return `Rp ${value.toLocaleString("id-ID")}`;
    },
  },
  grid: {
    left: "3%",
    right: "4%",
    bottom: "3%",
    containLabel: true,
  },
  xAxis: {
    type: "category",
    data: ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"],
    axisLine: { lineStyle: { color: "#555", type: "dashed" } },
    axisLabel: { show: true },
  },
  yAxis: {
    type: "value",
    axisLabel: {
      formatter: (value) => `Rp ${value.toLocaleString("id-ID")}`,
    },
    axisLine: { lineStyle: { color: "#555", type: "dashed" } },
    splitLine: {
      lineStyle: { type: "dashed", color: "#ccc" },
    },
  },
  series: [
    {
      name: "Pengeluaran",
      type: "bar",
      data: [15000, 10000, 25000, 5000, 30000, 0, 5000],
      itemStyle: {
        color: "#42A5F5",
      },
    },
  ],
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
          <q-card
            square
            class="no-shadow bordered"
            style="background-color: #fff"
          >
            <q-card-section class="q-pa-none">
              <v-chart class="chart" :option="pieChartOption" autoresize />
            </q-card-section>
          </q-card>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <q-card
            square
            class="no-shadow bordered"
            style="background-color: #fff"
          >
            <q-card-section class="q-pa-none">
              <v-chart class="chart" :option="barChartOption" autoresize />
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <div class="q-pa-sm q-pt-md">
      <div class="row q-col-gutter-sm">
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <div class="text-subtitle2 text-bold text-grey-8">
            Top Up Wallet Terkini
          </div>
          <q-card square class="no-shadow bordered q-mt-sm">
            <q-card-section class="q-pa-none">
              <q-list bordered>
                <q-item v-for="topup in latestTopups" :key="topup.id">
                  <q-item-section avatar>
                    <q-icon color="green" name="arrow_downward" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ topup.description }}</q-item-label>
                    <q-item-label caption>{{ topup.date }}</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-item-label class="text-green-8 text-bold">
                      +Rp {{ topup.amount.toLocaleString("id-ID") }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <div class="text-subtitle2 text-bold text-grey-8">
            Transaksi Terkini
          </div>
          <q-card square class="no-shadow bordered q-mt-sm">
            <q-card-section class="q-pa-none">
              <q-list bordered>
                <q-item
                  v-for="transaction in latestTransactions"
                  :key="transaction.id"
                >
                  <q-item-section avatar>
                    <q-icon color="red" name="arrow_upward" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ transaction.description }}</q-item-label>
                    <q-item-label caption>{{ transaction.date }}</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-item-label class="text-red-8 text-bold">
                      -Rp {{ transaction.amount.toLocaleString("id-ID") }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </customer-layout>
</template>

<style scoped>
.chart {
  height: 250px;
}
.q-card {
  width: 100%;
}
</style>

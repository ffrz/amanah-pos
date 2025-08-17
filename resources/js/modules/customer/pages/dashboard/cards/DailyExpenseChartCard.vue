<script setup>
import { ref } from "vue";

// Import ECharts
import VChart from "vue-echarts";
import { use } from "echarts/core";
import { BarChart } from "echarts/charts";
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
} from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";

use([
  CanvasRenderer,
  BarChart,
  TitleComponent,
  TooltipComponent,
  GridComponent,
]);

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
  <q-card square bordered flat class="q-pa-md" style="background-color: #fff">
    <q-card-section class="q-pa-none">
      <v-chart class="chart" :option="barChartOption" autoresize />
    </q-card-section>
  </q-card>
</template>

<style scoped>
.chart {
  height: 250px;
}
.q-card {
  width: 100%;
}
</style>

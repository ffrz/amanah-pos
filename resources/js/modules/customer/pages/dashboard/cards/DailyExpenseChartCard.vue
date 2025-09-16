<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3"; // Import usePage

// Import ECharts
import VChart from "vue-echarts";
import { use } from "echarts/core";
import { BarChart } from "echarts/charts";
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent, // Tambahkan LegendComponent
} from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";
import { formatNumber } from "@/helpers/formatter";

use([
  CanvasRenderer,
  BarChart,
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent, // Gunakan LegendComponent
]);

// Ambil data dari props yang dikirim oleh controller
const page = usePage();
const monthlyChartData = computed(() => page.props.monthlyChartData);

const barChartOption = ref({
  title: {
    text: "Transaksi Bulanan",
    left: "center",
    textStyle: { color: "#444", fontSize: 14 },
  },
  tooltip: {
    trigger: "axis",
    axisPointer: { type: "shadow" },
    formatter: (params) => {
      let tooltipContent = "";
      params.forEach((param) => {
        tooltipContent += `<br/>${param.marker} ${
          param.seriesName
        }: Rp ${formatNumber(param.value)}`;
      });
      return tooltipContent;
    },
  },
  legend: {
    data: ["Pengeluaran", "Pemasukan"],
    bottom: 0,
  },
  grid: {
    left: "3%",
    right: "4%",
    bottom: "15%", // Sesuaikan margin bawah karena ada legend
    containLabel: true,
  },
  xAxis: {
    type: "category",
    // Gunakan label (tanggal 1, 2, 3...) dari server
    data: monthlyChartData.value.labels,
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
      name: "Pemasukan",
      type: "bar",
      // Gunakan data pendapatan dari server
      data: monthlyChartData.value.income,
      itemStyle: {
        color: "#4CAF50", // Hijau untuk pendapatan
      },
    },
    {
      name: "Pengeluaran",
      type: "bar",
      // Gunakan data pengeluaran dari server
      data: monthlyChartData.value.expense,
      itemStyle: {
        color: "#F44336", // Merah untuk pengeluaran
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

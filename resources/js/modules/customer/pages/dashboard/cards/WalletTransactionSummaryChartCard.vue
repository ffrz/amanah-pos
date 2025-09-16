<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import VChart from "vue-echarts";
import { use } from "echarts/core";
import { PieChart } from "echarts/charts";
import { TitleComponent, TooltipComponent } from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";
import { formatNumber } from "@/helpers/formatter";

use([CanvasRenderer, PieChart, TitleComponent, TooltipComponent]);

const page = usePage();
const transactionsByTypeChartData = computed(
  () => page.props.transactionsByTypeChartData
);

const pieChartOption = ref({
  title: {
    text: "Rekap Transaksi Wallet",
    left: "center",
    textStyle: { color: "#444", fontSize: 14 },
  },
  tooltip: {
    trigger: "item",
    formatter: (params) => {
      const value = params.value;
      const name = params.name;
      const percent = params.percent;
      return `${name}<br/>Rp ${formatNumber(Math.abs(value))} (${percent}%)`;
    },
  },
  series: [
    {
      name: "Jenis Transaksi",
      type: "pie",
      radius: ["40%", "70%"],
      center: ["50%", "50%"],
      avoidLabelOverlap: false,
      label: {
        show: true,
        formatter: "{b}: {d}%",
        fontSize: "12",
        fontWeight: "bold",
        color: "#333",
      },
      // emphasis DIBUANG total untuk menonaktifkan semua efek hover
      labelLine: {
        show: true,
        length: 10,
        length2: 10,
        lineStyle: {
          color: "#aaa",
        },
      },
      data: transactionsByTypeChartData.value,
    },
  ],
});
</script>

<template>
  <q-card bordered square flat class="q-pa-md">
    <q-card-section class="q-pa-none">
      <v-chart class="chart" :option="pieChartOption" autoresize />
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

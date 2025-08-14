<script setup>
import { ref } from "vue";

// Import ECharts
import VChart from "vue-echarts";
import { use } from "echarts/core";
import { PieChart } from "echarts/charts";
import { TitleComponent, TooltipComponent } from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";

use([CanvasRenderer, PieChart, TitleComponent, TooltipComponent]);

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
  series: [
    {
      name: "Penggunaan Dana",
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

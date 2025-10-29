<script setup>
import { computed } from "vue";
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
import { usePage } from "@inertiajs/vue3";

use([
  CanvasRenderer,
  BarChart,
  PieChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
]);

const page = usePage();

const colors = ["#82B1FF", "#4DB6AC", "#FFB74D", "#9575CD"];

const barChartOptions = computed(() => ({
  title: {
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
    data: page.props.data.chart_data_1.labels,
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
      data: page.props.data.chart_data_1.data,
      itemStyle: {
        borderRadius: [5, 5, 0, 0],
        color: colors[0],
      },
    },
  ],
}));

const pieChartOptions = computed(() => ({
  title: {
    left: "center",
  },
  tooltip: {
    show: false,
  },
  legend: {
    show: false,
  },

  series: [
    {
      name: "Jumlah Transaksi",
      type: "pie",
      radius: ["40%", "70%"],
      data: page.props.data.revenue_by_category,
      label: {
        show: true,
        position: "outside",
        formatter: (params) => {
          const formattedValue = formatNumber(params.value);
          return `${params.percent}% - ${params.name}\nRp. ${formattedValue}\n`;
        },
        fontSize: 12,
        fontWeight: "bold",
        color: "#616161",
      },

      labelLine: {
        show: true,
        length: 10,
        length2: 10,
      },

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
</script>

<template>
  <div class="row q-col-gutter-sm q-pb-sm">
    <div class="col-md-6 col-12">
      <q-card square bordered flat class="full-width q-pa-md">
        <div class="text-subtitle1 q-pb-sm">Penjualan</div>
        <VChart class="chart" :option="barChartOptions" autoresize />
      </q-card>
    </div>
    <div class="col-md-6 col-12">
      <q-card square bordered flat class="full-width q-pa-md">
        <div class="text-subtitle1 q-pb-sm">Distribusi Omzet</div>
        <VChart class="chart" :option="pieChartOptions" autoresize />
      </q-card>
    </div>
  </div>
</template>

<style scoped>
.chart {
  height: 350px;
}
</style>

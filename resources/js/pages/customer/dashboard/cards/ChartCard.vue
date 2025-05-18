<template>
  <div class="row q-col-gutter-sm">
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
      <q-card
        square
        class="no-shadow"
        bordered
        style="background-color: #fff"
      >
        <q-card-section class="q-pa-none">
          <ECharts
            :option="monthly_orders_chart"
            class="q-mt-md"
            :resizable="true"
            autoresize
            style="height: 250px"
          />
        </q-card-section>
      </q-card>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
      <q-card
        class="no-shadow"
        square
        bordered
        style="background-color: #fff"
      >
        <q-card-section class="q-pa-none">
          <ECharts
            :option="monthly_closed_orders_chart"
            class="q-mt-md"
            :resizable="true"
            autoresize
            style="height: 250px"
          />
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import * as echarts from "echarts";
import ECharts from "vue-echarts";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const monthly_orders_chart = {
  tooltip: { show: true },
  title: {
    text: "Servis Diterima vs Sukses vs Gagal",
    textStyle: { color: "#444" },
    left: "center",
  },
  legend: {
    top: "10%",
    data: [
      page.props.data.chart1_data.data[0].label,
      page.props.data.chart1_data.data[1].label,
      page.props.data.chart1_data.data[2].label,
    ],
  },
  grid: { containLabel: true, left: "5px", bottom: "5px", right: "5px" },
  xAxis: {
    show: true,
    type: "category",
    data: page.props.data.chart1_data.x_axis_label_data,
    axisLine: { lineStyle: { color: "#555", type: "dashed" } },
    axisTick: {
      show: true,
      alignWithLabel: true,
      lineStyle: { show: true, color: "#ccc", type: "dashed" },
    },
    axisLabel: { show: true },
    boundaryGap: true,
  },
  yAxis: {
    show: true,
    type: "value",
    axisLine: { lineStyle: { color: "#555", type: "dashed" } },
    axisLabel: { show: true },
    splitLine: {
      lineStyle: { type: "dashed", color: "#ccc" },
    },
    axisTick: {
      show: true,
      lineStyle: { show: true, color: "#fff", type: "dashed" },
    },
  },
  series: [
    {
      name: page.props.data.chart1_data.data[0].label,
      type: "line",
      lineStyle: { color: "#007bff" },
      itemStyle: { color: "#007bff" },
      smooth: true,
      data: page.props.data.chart1_data.data[0].data,
    },
    {
      name: page.props.data.chart1_data.data[1].label,
      type: "line",
      lineStyle: { color: "#28a745" },
      itemStyle: { color: "#28a745" },
      smooth: true,
      data: page.props.data.chart1_data.data[1].data,
    },
    {
      name: page.props.data.chart1_data.data[2].label,
      type: "line",
      lineStyle: { color: "#dc3545" },
      itemStyle: { color: "#dc3545" },
      smooth: true,
      data: page.props.data.chart1_data.data[2].data,
    },
  ],
  color: ["white"],
};

const monthly_closed_orders_chart = {
  tooltip: { show: true },
  title: {
    text: "Pendapatan Servis",
    textStyle: { color: "#444" },
    left: "center",
  },
  legend: {
    top: "10%",
    data: [
      page.props.data.chart2_data.data[0].label,
    ],
  },
  grid: { containLabel: true, left: "5px", bottom: "5px", right: "5px" },
  xAxis: {
    show: true,
    type: "category",
    data: page.props.data.chart1_data.x_axis_label_data,
    axisLine: { lineStyle: { color: "#555", type: "dashed" } },
    axisTick: {
      show: true,
      alignWithLabel: true,
      lineStyle: { show: true, color: "#ccc", type: "dashed" },
    },
    axisLabel: { show: true },
    boundaryGap: true,
  },
  yAxis: {
    show: true,
    type: "value",
    axisLine: { lineStyle: { color: "#555", type: "dashed" } },
    axisLabel: { show: true },
    splitLine: {
      lineStyle: { type: "dashed", color: "#ccc" },
    },
    axisTick: {
      show: true,
      lineStyle: { show: true, color: "#fff", type: "dashed" },
    },
  },
  series: [
    {
      name: page.props.data.chart2_data.data[0].label,
      type: "bar",
      itemStyle: { color: "#007bff" },
      smooth: true,
      data: page.props.data.chart2_data.data[0].data,
    }
  ],
  color: ["white"],
};
</script>

<style scoped>
.q-card {
  width: 100%;
}
</style>

<script setup>
import { usePage } from "@inertiajs/vue3";
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";
import { createOptions } from "@/helpers/options";
import DateTimePicker from "@/components/DateTimePicker.vue";
import dayjs from "dayjs";

const page = usePage();
const title = "Laporan Rincian Penjualan";

const options = page.props.options;

options.initial_filter.start_date = dayjs().startOf("month").toDate();
options.initial_filter.end_date = dayjs().endOf("month").toDate();
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <BackButton :route="route('admin.report.index')" />
      </div>
    </template>
    <template #right-button></template>

    <ReportGeneratorLayout
      :options="options"
      routeName="admin.report.sales-order.generate"
    >
      <template #filter="{ form }">
        <DateTimePicker
          v-model="form.filter.start_date"
          label="Mulai Tanggal"
          class="col-xs-6 col-sm-2"
          hide-bottom-space
          date-only
        />
        <DateTimePicker
          v-model="form.filter.end_date"
          label="Sampai Tanggal"
          class="col-xs-6 col-sm-2"
          hide-bottom-space
          date-only
        />
      </template>
    </ReportGeneratorLayout>
  </authenticated-layout>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";
import { createOptions } from "@/helpers/options";
import DateTimePicker from "@/components/DateTimePicker.vue";
import dayjs from "dayjs";

const page = usePage();
const title = "Laporan Rincian Penjualan";

const primaryColumns = createOptions(page.props.primary_columns);
const optionalColumns = createOptions(page.props.optional_columns);
const initialColumns = page.props.initial_columns;
const templates = page.props.templates;

const initialFilter = {
  customer_ids: [],
  start_date: dayjs().startOf("month").toDate(),
  end_date: dayjs().endOf("month").toDate(),
};

const initialSortOptions = [
  {
    column: "code",
    order: "asc",
  },
];
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
      :primaryColumns="primaryColumns"
      :optionalColumns="optionalColumns"
      :initialColumns="initialColumns"
      :initialFilter="initialFilter"
      :initialSortOptions="initialSortOptions"
      :templates="templates"
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

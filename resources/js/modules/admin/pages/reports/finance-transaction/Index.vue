<script setup>
import { usePage } from "@inertiajs/vue3";
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import dayjs from "dayjs";
import { ref } from "vue"; // Diperlukan untuk state filter

const page = usePage();
const title = "Laporan Transaksi Keuangan";
const options = page.props.options;
options.initial_filter.start_date = dayjs().startOf("month").toDate();
options.initial_filter.end_date = dayjs().endOf("month").toDate();
options.initial_filter.accounts = [];
options.initial_filter.categories = [];
options.initial_filter.tags = [];
options.initial_filter.type = "all";

// 2. Memetakan Data untuk q-select

// Akun Keuangan (Multiselect)
const accounts = page.props.accounts.map((a) => ({
  label: a.name,
  value: a.id,
}));

// Kategori Transaksi (Select)
const categories = page.props.categories.map((c) => ({
  label: c.name,
  value: c.id,
}));
categories.unshift({ label: "Tanpa Kategori", value: "none" });

// Jenis Transaksi (Select)
const types = [
  { value: "all", label: "Semua" },
  { value: "income", label: "Pemasukan" },
  { value: "expense", label: "Pengeluaran" },
  { value: "transfer", label: "Transfer" },
];

const tags = page.props.tags.map((tag) => ({
  label: tag.name,
  value: tag.id,
}));
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
      routeName="admin.report.finance-transaction.generate"
    >
      <template #filter="{ form }">
        <DateTimePicker
          v-model="form.filter.start_date"
          label="Mulai Tanggal"
          class="col-xs-12 col-sm-6"
          date-only
          hide-bottom-space
        />

        <DateTimePicker
          v-model="form.filter.end_date"
          label="Sampai Tanggal"
          date-only
          hide-bottom-space
        />

        <q-select
          label="Akun"
          v-model="form.filter.accounts"
          :options="accounts"
          map-options
          emit-value
          use-chips
          multiple
          clearable
        />

        <q-select
          label="Jenis "
          v-model="form.filter.type"
          :options="types"
          map-options
          emit-value
        />

        <q-select
          label="Kategori "
          v-model="form.filter.categories"
          :options="categories"
          map-options
          emit-value
          clearable
          multiple
          use-chips
          class="col-xs-12 col-sm-6"
        />

        <q-select
          label="Label "
          v-model="form.filter.tags"
          :options="tags"
          map-options
          emit-value
          multiple
          use-chips
          clearable
          class="col-xs-12 col-sm-6"
        />
      </template>
    </ReportGeneratorLayout>
  </authenticated-layout>
</template>

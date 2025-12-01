<script setup>
import { usePage } from "@inertiajs/vue3";
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue"; // Sesuaikan path relative-nya
import BackButton from "@/components/BackButton.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import dayjs from "dayjs";

const page = usePage();
const title = "Laporan Rincian Biaya Operasional";
const options = page.props.options;

// 1. Setup Initial Filter
// Menggunakan tanggal awal & akhir bulan ini sebagai default
options.initial_filter.start_date = dayjs().startOf("month").toDate();
options.initial_filter.end_date = dayjs().endOf("month").toDate();
options.initial_filter.accounts = [];
options.initial_filter.categories = [];

// 2. Mapping Data untuk q-select (Dropdown)

// Akun Keuangan (Multiselect)
const accounts = page.props.accounts.map((a) => ({
  label: a.name,
  value: a.id,
}));

// Kategori Biaya (Multiselect)
const categories = page.props.categories.map((c) => ({
  label: c.name,
  value: c.id,
}));
// Tambahkan opsi 'Tanpa Kategori' di paling atas
categories.unshift({ label: "Tanpa Kategori / Umum", value: "none" });
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
      routeName="admin.report.operational-cost-detail.generate"
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
          class="col-xs-12 col-sm-6"
        />

        <q-select
          label="Akun Keuangan (Sumber Dana)"
          v-model="form.filter.accounts"
          :options="accounts"
          map-options
          emit-value
          use-chips
          multiple
          clearable
          class="col-xs-12"
        >
          <template #option="{ itemProps, opt, selected, toggleOption }">
            <q-item v-bind="itemProps">
              <q-item-section>
                <q-item-label>{{ opt.label }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-checkbox
                  :model-value="selected"
                  @update:model-value="toggleOption(opt)"
                />
              </q-item-section>
            </q-item>
          </template>
        </q-select>

        <q-select
          label="Kategori Biaya"
          v-model="form.filter.categories"
          :options="categories"
          map-options
          emit-value
          clearable
          multiple
          use-chips
          class="col-xs-12"
        >
          <template #option="{ itemProps, opt, selected, toggleOption }">
            <q-item v-bind="itemProps">
              <q-item-section>
                <q-item-label>{{ opt.label }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-checkbox
                  :model-value="selected"
                  @update:model-value="toggleOption(opt)"
                />
              </q-item-section>
            </q-item>
          </template>
        </q-select>
      </template>
    </ReportGeneratorLayout>
  </authenticated-layout>
</template>

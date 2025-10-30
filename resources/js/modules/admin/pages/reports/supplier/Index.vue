<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";
import { useQuasar } from "quasar";

const $q = useQuasar();
const title = "Laporan Supplier";

// Definisikan data yang unik untuk laporan ini
const primaryColumns = [
  { value: "code", label: "Kode" },
  { value: "name", label: "Nama" },
];

const optionalColumns = [
  { value: "phone_1", label: "No Telepon" },
  { value: "balance", label: "Saldo" },
];

const statusOptions = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

// Nilai awal filter dan sort yang unik
const initialFilter = {
  status: "all",
};

const initialSortOptions = [
  {
    column: "code", // Kode kolom default untuk sortir
    asc: true,
  },
];

// Gunakan ref untuk mengakses komponen template
const reportGeneratorRef = ref(null);

// Fungsi submit yang dipanggil dari template
const handleReportSubmit = ({ format, form }) => {
  // Di sini Anda dapat mengirim form.columns, form.filter, form.sortOptions, dll.
  // ke backend Anda menggunakan router.post/get atau axios.
  console.log("Form Data:", form);
  alert(`Mencetak laporan dalam format: ${format}`);
};
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
      ref="reportGeneratorRef"
      :primaryColumns="primaryColumns"
      :optionalColumns="optionalColumns"
      :initialFilter="initialFilter"
      :initialSortOptions="initialSortOptions"
      @submit="handleReportSubmit"
    >
      <template #filter="{ form }">
        <q-select
          label="Status"
          v-model="form.filter.status"
          :options="statusOptions"
          map-options
        />
      </template>

      <template #sort="{ form, columnOptions }">
        <div class="row q-col-gutter-sm items-center">
          <q-select
            v-model="form.sortOptions[0].column"
            class="col-grow"
            style="min-width: 150px"
            :options="columnOptions"
            map-options
            dense
          />
          <q-btn
            :icon="form.sortOptions[0].asc ? 'arrow_upward' : 'arrow_downward'"
            color="grey-8"
            flat
            round
            dense
            @click="form.sortOptions[0].asc = !form.sortOptions[0].asc"
            class="q-ml-sm"
          >
            <q-tooltip>
              Urutkan:
              {{
                form.sortOptions[0].asc
                  ? "Terkecil ke Terbesar"
                  : "Terbesar ke Terkecil"
              }}
            </q-tooltip>
          </q-btn>
        </div>
      </template>
    </ReportGeneratorLayout>
  </authenticated-layout>
</template>

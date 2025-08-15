<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { formatNumber } from "@/helpers/formatter";
import { getCurrentMonth, getCurrentYear } from "@/helpers/datetime";
import { createMonthOptions, createYearOptions } from "@/helpers/options";
import { router } from "@inertiajs/vue3";

// TODO:
// - Tambahkan kolom ID Konfirmasi misal #TP-00000011 untuk mudah melacak di sistem ketika followup
// - tambahkan halaman detail untuk melihat rincian status, kapan dikonfirmasi, dll.
// - method dibawah ini masih dummy, jadi perlu diimeplementasikan dan dirancang dari mulai database

const title = "Daftar Konfirmasi Pembayaran";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);

const currentYear = getCurrentYear();
const currentMonth = getCurrentMonth();

const yearOptions = [
  { label: "Semua Tahun", value: "all" },
  ...createYearOptions(currentYear - 2, currentYear).reverse(),
];

const monthOptions = [
  { value: "all", label: "Semua Bulan" },
  ...createMonthOptions(),
];

const statusOptions = [
  { value: "all", label: "Semua Status" },
  { value: "pending", label: "Pending" },
  { value: "confirmed", label: "Dikonfirmasi" },
  { value: "rejected", label: "Ditolak" },
];

const filter = reactive({
  search: "",
  year: currentYear,
  month: currentMonth,
  status: "all",
  ...getQueryParams(),
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "datetime",
  descending: true,
});

const columns = [
  {
    name: "datetime",
    label: "Tanggal",
    field: "datetime",
    align: "left",
    sortable: true,
  },
  {
    name: "destinationAccount",
    label: "Bank Tujuan",
    field: "destinationAccount",
    align: "left",
    sortable: true,
  },
  {
    name: "amount",
    label: "Jumlah (Rp.)",
    field: "amount",
    align: "right",
    sortable: true,
  },
  {
    name: "status",
    label: "Status",
    field: "status",
    align: "center",
    sortable: true,
  },
  { name: "aksi", label: "Aksi", field: "aksi", align: "center" },
];

// --- Fungsi untuk membuat data dummy ---
const generateDummyRows = () => {
  const dummyData = [
    {
      id: 1,
      datetime: "2024-08-15 10:00",
      destinationAccount: "Rek. Koperasi",
      amount: 500000,
      status: "pending",
      description: "",
      image_path: "https://amanah-pos.shift-apps.my.id/assets/no-image.jpg",
    },
    {
      id: 2,
      datetime: "2025-08-15 09:30",
      destinationAccount: "Rek. Bendahara",
      amount: 350000,
      status: "confirmed",
      description: "",
      image_path: "https://amanah-pos.shift-apps.my.id/assets/no-image.jpg",
    },
    {
      id: 3,
      datetime: "2025-07-15 15:20",
      destinationAccount: "Rek. Koperasi",
      amount: 750000,
      status: "confirmed",
      description: "",
      image_path: "https://amanah-pos.shift-apps.my.id/assets/no-image.jpg",
    },
    {
      id: 4,
      datetime: "2025-06-10 11:15",
      destinationAccount: "Rek. Bendahara",
      amount: 200000,
      status: "confirmed",
      description: "",
      image_path: "https://amanah-pos.shift-apps.my.id/assets/no-image.jpg",
    },
    {
      id: 5,
      datetime: "2025-05-12 18:45",
      destinationAccount: "Rek. Koperasi",
      amount: 600000,
      status: "confirmed",
      description: "",
      image_path: "https://amanah-pos.shift-apps.my.id/assets/no-image.jpg",
    },
  ];
  return dummyData;
};

// --- Mengganti fungsi fetchItems dengan logika data dummy ---
const fetchItems = () => {
  loading.value = true;
  // Simulasikan delay dari server
  setTimeout(() => {
    let dummyRows = generateDummyRows();

    // Logika filtering sederhana
    if (filter.status !== "all") {
      dummyRows = dummyRows.filter((row) => row.status === filter.status);
    }
    if (filter.search) {
      const searchTerm = filter.search.toLowerCase();
      dummyRows = dummyRows.filter(
        (row) =>
          row.namaWali.toLowerCase().includes(searchTerm) ||
          row.namaSantri.toLowerCase().includes(searchTerm) ||
          row.destinationAccount.toLowerCase().includes(searchTerm)
      );
    }

    rows.value = dummyRows;
    pagination.value.rowsNumber = dummyRows.length;
    loading.value = false;
  }, 500);
};

onMounted(() => {
  fetchItems();
});

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  // Kolom yang ditampilkan di layar kecil
  return columns.filter(
    (col) =>
      col.name === "datetime" || col.name === "status" || col.name === "aksi"
  );
});

// Aksi ketika tombol "Lihat Bukti" diklik
const showProof = (buktiUrl) => {
  window.open(buktiUrl, "_blank");
};
</script>

<template>
  <i-head :title="title" />
  <customer-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        icon="add"
        dense
        color="primary"
        @click="router.get(route('customer.wallet-topup-confirmation.add'))"
      />
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            v-model="filter.year"
            :options="yearOptions"
            label="Tahun"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.month"
            :options="monthOptions"
            label="Bulan"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            :disable="filter.year === null"
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.status"
            :options="statusOptions"
            label="Status"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />
          <q-input
            class="col"
            outlined
            dense
            debounce="300"
            v-model="filter.search"
            placeholder="Cari..."
            clearable
          >
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-sm">
      <q-table
        class="full-height-table"
        flat
        bordered
        square
        color="primary"
        row-key="id"
        virtual-scroll
        v-model:pagination="pagination"
        :filter="filter.search"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[10, 25, 50]"
        @request="fetchItems"
        binary-state-sort
      >
        <template v-slot:loading>
          <q-inner-loading showing color="red" />
        </template>
        <template v-slot:no-data="{ icon, message, filter }">
          <div class="full-width row flex-center text-grey-8 q-gutter-sm">
            <span>
              Tidak ada data konfirmasi
              {{ filter ? " dengan kata kunci '" + filter + "'" : "" }}</span
            >
          </div>
        </template>

        <template v-slot:body="props">
          <q-tr :props="props">
            <q-td key="datetime" :props="props" class="wrap-column">
              <div>
                <q-icon name="calendar_today" size="xs" />
                {{ props.row.datetime }}
              </div>
              <template v-if="!$q.screen.gt.sm">
                <div>
                  <q-icon name="account_balance" size="xs" />
                  Bank: {{ props.row.destinationAccount }}
                </div>
                <div>
                  <q-icon name="money" size="xs" />
                  Rp. {{ formatNumber(props.row.amount) }}
                </div>
                <div v-if="props.row.description">
                  <q-icon name="notes" />
                  {{ props.row.description }}
                </div>
              </template>
            </q-td>

            <q-td key="destinationAccount" :props="props">
              {{ props.row.destinationAccount }}
            </q-td>

            <q-td key="amount" :props="props" style="text-align: right">
              Rp. {{ formatNumber(props.row.amount) }}
            </q-td>

            <q-td key="description" :props="props">
              {{ props.row.description }}
            </q-td>

            <q-td key="status" :props="props" class="text-center">
              <q-chip
                dense
                square
                :color="
                  props.row.status === 'confirmed'
                    ? 'green'
                    : props.row.status === 'rejected'
                    ? 'red'
                    : 'grey'
                "
                :label="
                  props.row.status === 'confirmed'
                    ? 'Dikonfirmasi'
                    : props.row.status === 'rejected'
                    ? 'Ditolak'
                    : 'Pending'
                "
                text-color="white"
              />
            </q-td>

            <q-td key="aksi" :props="props" class="text-center">
              <q-btn
                icon="image"
                color="primary"
                dense
                flat
                @click="showProof(props.row.bukti)"
              >
                <q-tooltip>Lihat Bukti</q-tooltip>
              </q-btn>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </customer-layout>
</template>

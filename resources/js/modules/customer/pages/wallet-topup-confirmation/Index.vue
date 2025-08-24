<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { getCurrentMonth, getCurrentYear } from "@/helpers/datetime";
import { createMonthOptions, createYearOptions } from "@/helpers/options";
import { router } from "@inertiajs/vue3";
import { handleFetchItems } from "@/helpers/client-req-handler";
import ImageViewer from "@/components/ImageViewer.vue";

// TODO:
// - Tambahkan kolom ID Konfirmasi misal #TP-00000011 untuk mudah melacak di sistem ketika followup
// - tambahkan halaman detail untuk melihat rincian status, kapan dikonfirmasi, dll.
// - method dibawah ini masih dummy, jadi perlu diimeplementasikan dan dirancang dari mulai database

const title = "Konfirmasi Top Up Wallet";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const activeImagePath = ref(null);
const showImageViewer = ref(false);
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
  { value: "canceled", label: "Dibatalkan" },
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
  },
  {
    name: "finance_account_id",
    label: "Bank Tujuan",
    field: "finance_account_id",
    align: "left",
  },
  {
    name: "amount",
    label: "Jumlah (Rp.)",
    field: "amount",
    align: "right",
  },
  {
    name: "status",
    label: "Status",
    field: "status",
    align: "center",
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
  { name: "action", label: "Aksi", field: "action", align: "center" },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("customer.wallet-topup-confirmation.data"),
    loading,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter(
    (col) => col.name === "datetime" || col.name === "action"
  );
});

watch(
  () => filter.year,
  (newVal) => {
    if (newVal === null) {
      filter.month = null;
    }
  }
);

const showAttachment = (url) => {
  activeImagePath.value = url;
  showImageViewer.value = true;
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        icon="add"
        size="sm"
        dense
        color="primary"
        label="Konfirmasi&nbsp;"
        @click="router.get(route('customer.wallet-topup-confirmation.add'))"
      />
      <q-btn
        size="sm"
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
            v-if="filter.year !== 'all'"
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
    <div class="q-pa-xs">
      <q-table
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

        <template v-slot:body="props">
          <q-tr :props="props">
            <q-td key="datetime" :props="props" class="wrap-column">
              <div>
                <q-icon name="calendar_today" class="inline-icon" />
                {{ formatDateTime(props.row.datetime) }}
              </div>
              <template v-if="!$q.screen.gt.sm">
                <div>
                  <q-icon name="account_balance" class="inline-icon" />
                  Bank: {{ props.row.finance_account.name }}
                </div>
                <div>
                  <q-icon name="money" class="inline-icon" />
                  Rp. {{ formatNumber(props.row.amount) }}
                </div>
                <div v-if="props.row.description">
                  <q-icon name="notes" class="inline-icon" />
                  {{ props.row.description }}
                </div>
              </template>
            </q-td>

            <q-td key="finance_account_id" :props="props">
              {{ props.row.finance_account.name }}<br />
              {{ props.row.finance_account.bank }}
              {{ props.row.finance_account.number }} an.
              {{ props.row.finance_account.holder }}
            </q-td>

            <q-td key="amount" :props="props" style="text-align: right">
              Rp. {{ formatNumber(props.row.amount) }}
            </q-td>

            <q-td key="status" :props="props" class="text-center">
              <q-chip
                size="sm"
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
                  $CONSTANTS.CUSTOMER_WALLET_TRANSACTION_CONFIRMATION_STATUSES[
                    props.row.status
                  ]
                "
                text-color="white"
              />
            </q-td>
            <q-td key="notes" :props="props">
              {{ props.row.notes }}
            </q-td>
            <q-td key="action" :props="props" class="text-center">
              <q-btn
                icon="image"
                color="primary"
                dense
                flat
                :disable="props.row.image_path == null"
                @click="showAttachment(props.row.image_path)"
              >
                <q-tooltip v-if="props.row.image_path != null"
                  >Lihat Bukti</q-tooltip
                >
              </q-btn>
            </q-td>
          </q-tr>
        </template>
      </q-table>
      <ImageViewer
        v-model="showImageViewer"
        :imageUrl="`/${activeImagePath}`"
      />
    </div>
  </authenticated-layout>
</template>

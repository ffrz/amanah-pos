<script setup>
// core
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

// components
import ImageViewer from "@/components/ImageViewer.vue";
import LongTextView from "@/components/LongTextView.vue";
import CustomerWalletTransactionConfirmationStatusChip from "@/components/CustomerWalletTransactionConfirmationStatusChip.vue";

// composables
import useCustomerWalletTransactionConfirmationStatusOptions from "@/composables/useCustomerWalletTransactionConfirmationStatusOptions";

// utils
import { getQueryParams } from "@/helpers/utils";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { getCurrentMonth, getCurrentYear } from "@/helpers/datetime";
import { createMonthOptions, createYearOptions } from "@/helpers/options";
import { handleFetchItems } from "@/helpers/client-req-handler";
import { useTableHeight } from "@/composables/useTableHeight";

const title = "Konfirmasi Top Up Wallet";
const $q = useQuasar();
const showFilter = ref(false);
const filterBarRef = ref(null);
const rows = ref([]);
const loading = ref(true);
const activeImagePath = ref(null);
const showImageViewer = ref(false);
const currentYear = getCurrentYear();
const yearOptions = createYearOptions(currentYear - 2, currentYear, true, true);
const monthOptions = createMonthOptions(true);
const statusOptions =
  useCustomerWalletTransactionConfirmationStatusOptions(true);

const filter = reactive({
  search: "",
  year: currentYear,
  month: getCurrentMonth(),
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
    label: $q.screen.lt.md ? "Item" : "Tanggal",
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
  return columns.filter((col) => ["datetime", "action"].includes(col.name));
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

const { tableHeight } = useTableHeight(filterBarRef);
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title
      ><span class="text-subtitle2">{{ title }}</span></template
    >
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
      <q-toolbar class="filter-bar" ref="filterBarRef">
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
    <div class="q-pa-sm">
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
        :style="{ height: tableHeight }"
      >
        <template v-slot:loading>
          <q-inner-loading showing color="red" />
        </template>

        <template v-slot:body="props">
          <q-tr :props="props">
            <q-td key="datetime" :props="props" class="wrap-column">
              # {{ props.row.formatted_id }}
              <div>
                <q-icon name="calendar_today" class="inline-icon" />
                {{ formatDateTime(props.row.datetime) }}
              </div>
              <template v-if="!$q.screen.gt.sm">
                <LongTextView
                  icon="account_balance"
                  :text="props.row.finance_account.name"
                  :maxLength="30"
                />
                <div>
                  <q-icon name="money" class="inline-icon" />
                  Rp. {{ formatNumber(props.row.amount) }}
                </div>
                <div v-if="props.row.description">
                  <q-icon name="notes" class="inline-icon" />
                  {{ props.row.description }}
                </div>
                <CustomerWalletTransactionConfirmationStatusChip
                  :status="props.row.status"
                />
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
              <CustomerWalletTransactionConfirmationStatusChip
                :status="props.row.status"
              />
            </q-td>
            <q-td key="notes" :props="props">
              <LongTextView :text="props.row.notes" :maxLength="30" />
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
                <q-tooltip v-if="props.row.image_path != null">
                  Lihat Bukti
                </q-tooltip>
              </q-btn>
              <q-btn
                v-if="props.row.status == 'pending'"
                icon="cancel"
                color="red"
                dense
                flat
                :disable="props.row.image_path == null"
                @click="showAttachment(props.row.image_path)"
              >
                <q-tooltip>Batalkan</q-tooltip>
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

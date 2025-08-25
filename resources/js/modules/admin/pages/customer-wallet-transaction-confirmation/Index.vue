<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import {
  formatDateTime,
  formatDateTimeFromNow,
  formatNumber,
} from "@/helpers/formatter";
import { getCurrentMonth, getCurrentYear } from "@/helpers/datetime";
import {
  createMonthOptions,
  createOptions,
  createYearOptions,
} from "@/helpers/options";

import {
  handleDelete,
  handleFetchItems,
  handlePost,
} from "@/helpers/client-req-handler";
import ImageViewer from "@/components/ImageViewer.vue";
import StatusChip from "@/components/StatusChip.vue";
import CustomerWalletTransactionConfirmationStatusChip from "@/components/CustomerWalletTransactionConfirmationStatusChip.vue";

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
  ...createOptions(
    window.CONSTANTS.CUSTOMER_WALLET_TRANSACTION_CONFIRMATION_STATUSES
  ),
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
    url: route("admin.customer-wallet-transaction-confirmation.data"),
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

const acceptItem = (row) =>
  handlePost({
    message: `Setujui konfirmasi transaksi #${row.id}?`,
    url: route("admin.customer-wallet-transaction-confirmation.save", row.id),
    fetchItemsCallback: fetchItems,
    loading,
    data: {
      id: row.id,
      action: "accept",
    },
  });

const rejectItem = (row) =>
  handlePost({
    message: `Tolak konfirmasi transaksi #${row.id}?`,
    url: route("admin.customer-wallet-transaction-confirmation.save", row.id),
    fetchItemsCallback: fetchItems,
    loading,
    data: {
      id: row.id,
      action: "reject",
    },
  });

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus konfirmasi transaksi #${row.id}?`,
    url: route("admin.customer-wallet-transaction-confirmation.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

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
      >
        <template v-slot:loading>
          <q-inner-loading showing color="red" />
        </template>

        <template v-slot:body="props">
          <q-tr :props="props">
            <q-td key="datetime" :props="props" class="wrap-column">
              <div>
                <q-icon name="calendar_today" class="inline-icon" />
                {{ formatDateTime(props.row.datetime) }} ({{
                  formatDateTimeFromNow(props.row.datetime)
                }})
              </div>
              <template v-if="!$q.screen.gt.sm">
                <div>
                  <q-icon name="account_balance" class="inline-icon" />
                  {{ props.row.finance_account.name }}
                </div>
                <div>
                  <q-icon name="person" class="inline-icon" />
                  {{ props.row.customer.username }} -
                  {{ props.row.customer.name }}
                </div>
                <div>
                  <q-icon name="money" class="inline-icon" />
                  Rp. {{ formatNumber(props.row.amount) }}
                </div>
                <div v-if="props.row.description">
                  <q-icon name="notes" class="inline-icon" />
                  {{ props.row.description }}
                </div>
                <div>
                  <CustomerWalletTransactionConfirmationStatusChip
                    :status="props.row.status"
                  />
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
              <CustomerWalletTransactionConfirmationStatusChip
                :status="props.row.status"
              />
            </q-td>
            <q-td key="notes" :props="props">
              {{ props.row.notes }}
            </q-td>
            <q-td key="action" :props="props" class="text-center">
              <div class="flex justify-end">
                <q-btn
                  icon="more_vert"
                  dense
                  flat
                  style="height: 40px; width: 30px"
                  @click.stop
                >
                  <q-menu
                    anchor="bottom right"
                    self="top right"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-list style="width: 200px">
                      <q-item
                        @click.stop="acceptItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="check" />
                        </q-item-section>
                        <q-item-section>Setujui</q-item-section>
                      </q-item>
                      <q-item
                        @click.stop="rejectItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="cancel" />
                        </q-item-section>
                        <q-item-section>Tolak</q-item-section>
                      </q-item>
                      <q-separator />
                      <q-item
                        @click.stop="deleteItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="delete_forever" />
                        </q-item-section>
                        <q-item-section>Hapus</q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                </q-btn>
              </div>
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

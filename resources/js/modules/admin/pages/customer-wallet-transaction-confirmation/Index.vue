<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { router } from "@inertiajs/vue3";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { createOptions } from "@/helpers/options";
import {
  handleDelete,
  handleFetchItems,
  handlePost,
} from "@/helpers/client-req-handler";
import ImageViewer from "@/components/ImageViewer.vue";
import CustomerWalletTransactionConfirmationStatusChip from "@/components/CustomerWalletTransactionConfirmationStatusChip.vue";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";
import dayjs from "dayjs";
import DateTimePicker from "@/components/DateTimePicker.vue";

const title = "Konfirmasi Top Up Wallet";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const activeImagePath = ref(null);
const showImageViewer = ref(false);
const startDate = dayjs().startOf("month").toDate();
const endDate = dayjs().endOf("month").toDate();

const statusOptions = [
  { value: "all", label: "Semua Status" },
  ...createOptions(
    window.CONSTANTS.CUSTOMER_WALLET_TRANSACTION_CONFIRMATION_STATUSES
  ),
];

const filter = reactive({
  search: "",
  start_date: startDate,
  end_date: endDate,
  status: "all",
  ...getQueryParams(),
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
});

const columns = [
  {
    name: "code",
    label: "Kode",
    field: "code",
    align: "left",
    sortable: true,
  },
  {
    name: "datetime",
    label: "Waktu",
    field: "datetime",
    align: "left",
    sortable: true,
  },
  {
    name: "customer_id",
    label: "Pelanggan",
    field: "customer_id",
    align: "left",
    sortable: true,
  },
  {
    name: "finance_account_id",
    label: "Akun",
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
  { name: "action", field: "action", align: "center" },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = (props = null) => {
  const apiFilter = {
    ...filter,
    start_date: dayjs(filter.start_date).format("YYYY-MM-DD"),
    end_date: dayjs(filter.end_date).format("YYYY-MM-DD"),
  };

  handleFetchItems({
    pagination,
    filter: apiFilter,
    props,
    rows,
    url: route("admin.customer-wallet-transaction-confirmation.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => ["code", "action"].includes(col.name));
});

const acceptItem = (row) =>
  handlePost({
    message: `Setujui konfirmasi transaksi #${row.code}?`,
    url: route("admin.customer-wallet-transaction-confirmation.save"),
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
    url: route("admin.customer-wallet-transaction-confirmation.save"),
    fetchItemsCallback: fetchItems,
    loading,
    data: {
      id: row.id,
      action: "reject",
    },
  });

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus konfirmasi transaksi ${row.code}?`,
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
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        flat
        rounded
        dense
        @click="showFilter = !showFilter"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <DateTimePicker
            v-model="filter.start_date"
            label="Mulai Tanggal"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            @update:model-value="onFilterChange"
            hide-bottom-space
            date-only
          />
          <DateTimePicker
            v-model="filter.end_date"
            label="Sampai Tanggal"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            @update:model-value="onFilterChange"
            hide-bottom-space
            date-only
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
        ref="tableRef"
        class="full-height-table"
        :style="{ height: tableHeight }"
        flat
        bordered
        square
        color="primary"
        row-key="id"
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
          <q-tr
            :props="props"
            class="cursor-pointer"
            @click.stop="
              router.get(
                route('admin.customer-wallet-transaction-confirmation.detail', {
                  id: props.row.id,
                })
              )
            "
          >
            <q-td key="code" :props="props" class="wrap-column">
              <div>
                <q-icon class="inline-icon" name="tag" />
                {{ props.row.code }}
              </div>
              <div v-if="!$q.screen.gt.sm">
                <q-icon name="calendar_clock" class="inline-icon" />
                {{ formatDateTime(props.row.datetime) }}
              </div>
              <template v-if="!$q.screen.gt.sm">
                <LongTextView
                  icon="account_balance"
                  :text="props.row.finance_account.name"
                />
                <div>
                  <q-icon name="person" class="inline-icon" />
                  {{ props.row.customer.code }} -
                  {{ props.row.customer.name }}
                </div>
                <div>
                  <q-icon name="money" class="inline-icon" />
                  Rp. {{ formatNumber(props.row.amount) }}
                </div>
                <LongTextView
                  v-if="props.row.notes"
                  icon="notes"
                  :text="props.row.notes"
                />
                <div>
                  <CustomerWalletTransactionConfirmationStatusChip
                    :status="props.row.status"
                  />
                </div>
              </template>
            </q-td>
            <q-td key="datetime" :props="props" class="wrap-column">
              {{ formatDateTime(props.row.datetime) }}
            </q-td>
            <q-td key="customer_id" :props="props">
              <LongTextView
                :text="
                  props.row.customer.code + ' - ' + props.row.customer.name
                "
              />
            </q-td>
            <q-td key="finance_account_id" :props="props">
              {{ props.row.finance_account.name }}<br />
              {{ props.row.finance_account.bank }}
              {{ props.row.finance_account.number }} an.
              {{ props.row.finance_account.holder }}
            </q-td>
            <q-td key="amount" :props="props" style="text-align: right">
              {{ formatNumber(props.row.amount) }}
            </q-td>

            <q-td key="status" :props="props" class="text-center">
              <CustomerWalletTransactionConfirmationStatusChip
                :status="props.row.status"
              />
            </q-td>
            <q-td key="notes" :props="props" class="word-wrap">
              <LongTextView icon="notes" :text="props.row.notes" />
            </q-td>
            <q-td key="action" :props="props" class="text-center">
              <div class="flex justify-end no-wrap">
                <q-btn
                  icon="image"
                  color="primary"
                  dense
                  flat
                  :disable="
                    props.row.image_path === null || props.row.image_path === ''
                  "
                  @click.stop="showAttachment(props.row.image_path)"
                >
                  <q-tooltip v-if="props.row.image_path != null">
                    Lihat Bukti
                  </q-tooltip>
                </q-btn>
                <q-btn
                  v-if="
                    $can(
                      'admin.customer-wallet-transaction-confirmation:accept'
                    ) ||
                    $can(
                      'admin.customer-wallet-transaction-confirmation:deny'
                    ) ||
                    $can(
                      'admin.customer-wallet-transaction-confirmation.delete'
                    )
                  "
                  icon="more_vert"
                  dense
                  flat
                  rounded
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
                        v-if="
                          $can(
                            'admin.customer-wallet-transaction-confirmation:accept'
                          )
                        "
                        @click.stop="acceptItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                        :disable="props.row.status !== 'pending'"
                      >
                        <q-item-section avatar>
                          <q-icon name="check" />
                        </q-item-section>
                        <q-item-section>Setujui</q-item-section>
                      </q-item>
                      <q-item
                        v-if="
                          $can(
                            'admin.customer-wallet-transaction-confirmation:deny'
                          )
                        "
                        @click.stop="rejectItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                        :disable="props.row.status !== 'pending'"
                      >
                        <q-item-section avatar>
                          <q-icon name="cancel" />
                        </q-item-section>
                        <q-item-section>Tolak</q-item-section>
                      </q-item>
                      <q-separator />
                      <q-item
                        v-if="
                          $can(
                            'admin.customer-wallet-transaction-confirmation.delete'
                          )
                        "
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
